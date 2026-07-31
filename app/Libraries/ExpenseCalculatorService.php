<?php

namespace App\Libraries;

use App\Entities\Expense as ExpenseEntity;
use App\Entities\ExpenseType as ExpenseTypeEntity;
use App\Models\AbsentDay as AbsentDayModel;
use App\Models\Expense as ExpenseModel;
use App\Models\ExpenseInvolvement as ExpenseInvolvementModel;
use App\Models\ExpenseType as ExpenseTypeModel;
use App\Models\FinalDistribution as FinalDistributionModel;

/**
 * Calculates and persists each user's final monthly distribution based on
 * expenses recorded against a given billing month.
 *
 * Chapati-related logic has been removed — this project no longer has a
 * chapati module. Only the "other expenses" (equal | daysPresent) flow
 * remains.
 */
class ExpenseCalculatorService
{
    private ExpenseModel $expenseModel;
    private ExpenseTypeModel $expenseTypeModel;
    private ExpenseInvolvementModel $expenseInvolvementModel;
    private AbsentDayModel $absentDayModel;
    private FinalDistributionModel $finalDistributionModel;

    public function __construct()
    {
        $this->expenseModel = new ExpenseModel();
        $this->expenseTypeModel = new ExpenseTypeModel();
        $this->expenseInvolvementModel = new ExpenseInvolvementModel();
        $this->absentDayModel = new AbsentDayModel();
        $this->finalDistributionModel = new FinalDistributionModel();
    }

    /**
     * Calculate and persist the final distribution for every user in a given
     * billing month.
     *
     * @param  string $month Format: YYYY-MM (e.g. "2026-02")
     * @return array<int, array{expenses_amount: float, advance: float}>
     *                       Raw per-user accumulator keyed by user_id.
     */
    public function calculateFinalDistribution(string $month): array
    {
        // Wipe any previously generated rows for this month so re-running
        // generation is idempotent.
        $this->finalDistributionModel->where('month', $month)->delete();

        // $dist[user_id] = ['expenses_amount' => float, 'advance' => float]
        $dist = [];

        $this->applyOtherExpenses($month, $dist);
        $this->persistDistribution($month, $dist);

        return $dist;
    }

    /**
     * SECTION 1 — Other expenses (split_method: equal | daysPresent).
     *
     * Every expense whose billing_month matches the requested month is
     * processed:
     *   - equal       → each involved user pays an equal share.
     *   - daysPresent → share is proportional to days present in the
     *                   expense's date range; absences come from the
     *                   `absent_days` table, keyed by expense_id + user_id.
     *   - custom      → logged as a warning; not implemented.
     *
     * The payer (paid_by) always gets the full expense amount credited as
     * advance, regardless of split method.
     *
     * @param array<int, array{expenses_amount: float, advance: float}> $dist
     */
    private function applyOtherExpenses(string $month, array &$dist): void
    {
        $expenses = $this->expenseModel
            ->where('billing_month', $month)
            ->findAll();

        // Lazy-load cache: absent_days per expense, loaded on first
        // daysPresent encounter for that expense. Avoids N+1 while not
        // loading data that is never needed (e.g. months where every
        // expense is split equally).
        // Structure: $absentByExpense[expense_id][user_id] = days_absent
        $absentByExpense = [];

        foreach ($expenses as $expense) {
            /** @var ExpenseTypeEntity|null $type */
            $type = $this->expenseTypeModel->find($expense->expense_type_id);

            if ($type === null) {
                log_message('warning', "ExpenseCalculatorService: expense #{$expense->id} has unknown type, skipped.");
                continue;
            }

            $userIds = $this->getInvolvedUserIds($expense->id);

            if (empty($userIds)) {
                log_message('warning', "ExpenseCalculatorService: expense #{$expense->id} has no involvement records, skipped.");
                continue;
            }

            $this->creditPayerAdvance($dist, $expense);

            switch ($type->split_method) {
                case 'equal':
                    $this->splitEqual($dist, $expense, $userIds);
                    break;

                case 'daysPresent':
                    $this->splitByDaysPresent($dist, $expense, $userIds, $absentByExpense);
                    break;

                case 'custom':
                    log_message('warning', "ExpenseCalculatorService: expense #{$expense->id} uses split_method='custom' which is not yet implemented.");
                    break;

                default:
                    log_message('error', "ExpenseCalculatorService: expense #{$expense->id} has unknown split_method '{$type->split_method}', skipped.");
                    break;
            }
        }
    }

    /**
     * @return int[] User IDs involved in the given expense.
     */
    private function getInvolvedUserIds(int $expenseId): array
    {
        $involved = $this->expenseInvolvementModel
            ->where('expense_id', $expenseId)
            ->findAll();

        return array_map(static fn($i) => (int) $i->user_id, $involved);
    }

    /**
     * Credit the expense's payer with the full amount as an advance.
     *
     * @param array<int, array{expenses_amount: float, advance: float}> $dist
     */
    private function creditPayerAdvance(array &$dist, ExpenseEntity $expense): void
    {
        if (empty($expense->paid_by)) {
            return;
        }

        $payerId = (int) $expense->paid_by;
        $this->initUser($dist, $payerId);
        $dist[$payerId]['advance'] += (float) $expense->amount;
    }

    /**
     * Split an expense amount equally among involved users.
     *
     * @param array<int, array{expenses_amount: float, advance: float}> $dist
     * @param int[] $userIds
     */
    private function splitEqual(array &$dist, ExpenseEntity $expense, array $userIds): void
    {
        $share = (float) $expense->amount / count($userIds);

        foreach ($userIds as $uid) {
            $this->initUser($dist, $uid);
            $dist[$uid]['expenses_amount'] += $share;
        }
    }

    /**
     * Split an expense amount proportionally to each user's days present
     * within the expense's date range.
     *
     * @param array<int, array{expenses_amount: float, advance: float}> $dist
     * @param int[] $userIds
     * @param array<int, array<int, int>> $absentByExpense Lazy cache, passed
     *                                                      by reference.
     */
    private function splitByDaysPresent(array &$dist, ExpenseEntity $expense, array $userIds, array &$absentByExpense): void
    {
        $from = $this->toTimestamp($expense->from_date);
        $to = $this->toTimestamp($expense->to_date);
        $totalDays = (int) floor(($to - $from) / 86400) + 1;

        if ($totalDays <= 0) {
            log_message('warning', "ExpenseCalculatorService: expense #{$expense->id} has zero/negative day range, skipped.");
            return;
        }

        if (!isset($absentByExpense[$expense->id])) {
            $absentByExpense[$expense->id] = $this->loadAbsentDays($expense->id);
        }

        $absentMap = $absentByExpense[$expense->id];

        $presentDays = [];
        $sumPresentDays = 0;

        foreach ($userIds as $uid) {
            $daysAbsent = $absentMap[$uid] ?? 0;
            $days = max(0, $totalDays - $daysAbsent);
            $presentDays[$uid] = $days;
            $sumPresentDays += $days;
        }

        if ($sumPresentDays <= 0) {
            log_message('warning', "ExpenseCalculatorService: expense #{$expense->id} — all involved users have 0 present days, share not distributed.");
            return;
        }

        foreach ($presentDays as $uid => $days) {
            $share = ($days / $sumPresentDays) * (float) $expense->amount;
            $this->initUser($dist, $uid);
            $dist[$uid]['expenses_amount'] += $share;
        }
    }

    /**
     * Load days_absent per user for a given expense.
     *
     * @return array<int, int> [user_id => days_absent]
     */
    private function loadAbsentDays(int $expenseId): array
    {
        $rows = $this->absentDayModel
            ->where('expense_id', $expenseId)
            ->findAll();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row->user_id] = (int) $row->days_absent;
        }

        return $map;
    }

    /**
     * SECTION 2 — Persist final distribution.
     *
     * For each user in the accumulator:
     *   total_share    = expenses_amount
     *   advance_amount = total amount this user actually paid out
     *   balance        = total_share − advance_amount
     *
     *   balance > 0  → user still owes money    → due_amount = balance
     *   balance <= 0 → user overpaid / in credit → due_amount = 0
     *
     * final_amount stores the signed balance:
     *   positive = user owes this much
     *   negative = user is in credit by this much
     *
     * @param array<int, array{expenses_amount: float, advance: float}> $dist
     */
    private function persistDistribution(string $month, array $dist): void
    {
        foreach ($dist as $uid => $row) {
            $other = (float) $row['expenses_amount'];
            $advancePaid = (float) $row['advance'];

            $balance = $other - $advancePaid;
            $dueAmount = $balance > 0 ? $balance : 0.0;

            $insertData = [
                'user_id' => $uid,
                'month' => $month,
                'expenses_amount' => round($other, 0),
                'advance_amount' => round($advancePaid, 0),
                'due_amount' => round($dueAmount, 0),
                'final_amount' => round($balance, 0),
                'generated_at' => date('Y-m-d H:i:s'),
            ];

            if (!$this->finalDistributionModel->insert($insertData)) {
                log_message('error', "ExpenseCalculatorService: failed to insert final_distribution for user #{$uid}: " . json_encode($this->finalDistributionModel->errors()));
            }
        }
    }

    /**
     * Initialise an empty per-user accumulator if it does not exist yet.
     *
     * @param array<int, array{expenses_amount: float, advance: float}> $dist
     */
    private function initUser(array &$dist, int $uid): void
    {
        if (!isset($dist[$uid])) {
            $dist[$uid] = [
                'expenses_amount' => 0.0,
                'advance' => 0.0,
            ];
        }
    }

    /**
     * Safely convert a value that may be a CodeIgniter Time object, a plain
     * string, or null into a Unix timestamp.
     *
     * WHY THIS HELPER EXISTS:
     * CodeIgniter 4 Entity fields cast as 'datetime' return a Time object
     * (which extends PHP's DateTime). PHP's strtotime() only accepts
     * strings — passing a DateTime/Time object causes strtotime() to
     * silently return false, which then casts to 0 in integer arithmetic.
     * This makes every date-range calculation produce
     * totalDays = floor((0 - 0) / 86400) + 1 = 1, regardless of the actual
     * date range.
     *
     * Casting to string first calls Time::__toString(), which returns the
     * ISO datetime string (e.g. "2026-03-01 00:00:00") that strtotime()
     * can parse.
     */
    private function toTimestamp($date): int
    {
        if ($date === null) {
            return 0;
        }

        $ts = strtotime((string) $date);

        return $ts !== false ? $ts : 0;
    }
}
