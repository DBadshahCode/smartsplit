<?php

namespace App\Libraries;

use App\Models\AbsentDay as AbsentDayModel;
use App\Models\Expense as ExpenseModel;
use App\Models\ExpenseInvolvement as ExpenseInvolvementModel;
use App\Models\FinalDistribution as FinalDistributionModel;
use CodeIgniter\Database\BaseConnection;

/**
 * Calculates and persists each user's final monthly distribution based on
 * expenses recorded against a given billing month.
 *
 * Chapati-related logic has been removed — this project no longer has a
 * chapati module. Only the "expenses" (equal | daysPresent) flow
 * remains.
 *
 * Query strategy: everything the month needs is read up front in three
 * queries (expenses joined to their type, all involvements, all absences) and
 * the split maths then runs entirely in memory. Persisting is a single batch
 * insert. The whole wipe-and-rebuild runs inside one transaction so a failure
 * cannot leave the month with its old rows deleted and no new ones.
 */
class ExpenseCalculatorService
{
    private ExpenseModel $expenseModel;
    private ExpenseInvolvementModel $expenseInvolvementModel;
    private AbsentDayModel $absentDayModel;
    private FinalDistributionModel $finalDistributionModel;
    private BaseConnection $db;

    public function __construct()
    {
        $this->expenseModel = new ExpenseModel();
        $this->expenseInvolvementModel = new ExpenseInvolvementModel();
        $this->absentDayModel = new AbsentDayModel();
        $this->finalDistributionModel = new FinalDistributionModel();

        $this->db = db_connect();
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
        // $dist[user_id] = ['expenses_amount' => float, 'advance' => float]
        $dist = [];

        $this->applyOtherExpenses($month, $dist);

        $this->db->transStart();

        // Wipe any previously generated rows for this month so re-running
        // generation is idempotent.
        $this->finalDistributionModel->where('month', $month)->delete();

        $this->persistDistribution($month, $dist);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            log_message('error', "ExpenseCalculatorService: transaction failed while persisting distribution for {$month}.");
        }

        return $dist;
    }

    /**
     * SECTION 1 — Expenses (split_method: equal | daysPresent).
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
        $expenses = $this->expenseModel->getExpensesForMonth($month);

        if ($expenses === []) {
            return;
        }

        $expenseIds = array_map(
            static fn(array $expense): int => (int) $expense['id'],
            $expenses
        );

        // One query for every involvement in the month, instead of one per
        // expense.
        $involvementsByExpense = $this->expenseInvolvementModel
            ->getUserIdsByExpenseIds($expenseIds);

        // One query for the absences of the daysPresent expenses only — the
        // other split methods never read this data.
        $daysPresentIds = [];

        foreach ($expenses as $expense) {
            if (($expense['split_method'] ?? null) === 'daysPresent') {
                $daysPresentIds[] = (int) $expense['id'];
            }
        }

        // Structure: $absentByExpense[expense_id][user_id] = days_absent
        $absentByExpense = $this->absentDayModel
            ->getDaysByExpenseIds($daysPresentIds);

        foreach ($expenses as $expense) {
            $expenseId = (int) $expense['id'];
            $splitMethod = $expense['split_method'];

            // type_id is null only when the joined expense type is missing.
            if ($expense['type_id'] === null) {
                log_message('warning', "ExpenseCalculatorService: expense #{$expenseId} has unknown type, skipped.");
                continue;
            }

            $userIds = $involvementsByExpense[$expenseId] ?? [];

            if ($userIds === []) {
                log_message('warning', "ExpenseCalculatorService: expense #{$expenseId} has no involvement records, skipped.");
                continue;
            }

            $this->creditPayerAdvance($dist, $expense);

            switch ($splitMethod) {
                case 'equal':
                    $this->splitEqual($dist, $expense, $userIds);
                    break;

                case 'daysPresent':
                    $this->splitByDaysPresent(
                        $dist,
                        $expense,
                        $userIds,
                        $absentByExpense[$expenseId] ?? []
                    );
                    break;

                case 'custom':
                    log_message('warning', "ExpenseCalculatorService: expense #{$expenseId} uses split_method='custom' which is not yet implemented.");
                    break;

                default:
                    log_message('error', "ExpenseCalculatorService: expense #{$expenseId} has unknown split_method '{$splitMethod}', skipped.");
                    break;
            }
        }
    }

    /**
     * Credit the expense's payer with the full amount as an advance.
     *
     * @param array<int, array{expenses_amount: float, advance: float}> $dist
     * @param array<string, mixed> $expense
     */
    private function creditPayerAdvance(array &$dist, array $expense): void
    {
        if (empty($expense['paid_by'])) {
            return;
        }

        $payerId = (int) $expense['paid_by'];
        $this->initUser($dist, $payerId);
        $dist[$payerId]['advance'] += (float) $expense['amount'];
    }

    /**
     * Split an expense amount equally among involved users.
     *
     * @param array<int, array{expenses_amount: float, advance: float}> $dist
     * @param array<string, mixed> $expense
     * @param int[] $userIds
     */
    private function splitEqual(array &$dist, array $expense, array $userIds): void
    {
        $share = (float) $expense['amount'] / count($userIds);

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
     * @param array<string, mixed> $expense
     * @param int[] $userIds
     * @param array<int, int> $absentMap [user_id => days_absent], prefetched.
     */
    private function splitByDaysPresent(array &$dist, array $expense, array $userIds, array $absentMap): void
    {
        $expenseId = (int) $expense['id'];

        $from = $this->toTimestamp($expense['from_date']);
        $to = $this->toTimestamp($expense['to_date']);
        $totalDays = (int) floor(($to - $from) / 86400) + 1;

        if ($totalDays <= 0) {
            log_message('warning', "ExpenseCalculatorService: expense #{$expenseId} has zero/negative day range, skipped.");
            return;
        }

        $presentDays = [];
        $sumPresentDays = 0;

        foreach ($userIds as $uid) {
            $daysAbsent = $absentMap[$uid] ?? 0;
            $days = max(0, $totalDays - $daysAbsent);
            $presentDays[$uid] = $days;
            $sumPresentDays += $days;
        }

        if ($sumPresentDays <= 0) {
            log_message('warning', "ExpenseCalculatorService: expense #{$expenseId} — all involved users have 0 present days, share not distributed.");
            return;
        }

        $amount = (float) $expense['amount'];

        foreach ($presentDays as $uid => $days) {
            $share = ($days / $sumPresentDays) * $amount;
            $this->initUser($dist, $uid);
            $dist[$uid]['expenses_amount'] += $share;
        }
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
        if ($dist === []) {
            return;
        }

        $generatedAt = date('Y-m-d H:i:s');
        $rows = [];

        foreach ($dist as $uid => $row) {
            $other = (float) $row['expenses_amount'];
            $advancePaid = (float) $row['advance'];

            $balance = $other - $advancePaid;
            $dueAmount = $balance > 0 ? $balance : 0.0;

            $rows[] = [
                'user_id' => $uid,
                'month' => $month,
                'expenses_amount' => round($other, 0),
                'advance_amount' => round($advancePaid, 0),
                'due_amount' => round($dueAmount, 0),
                'final_amount' => round($balance, 0),
                'generated_at' => $generatedAt,
            ];
        }

        // One INSERT for every user instead of one per user.
        if ($this->finalDistributionModel->insertBatch($rows) === false) {
            log_message('error', 'ExpenseCalculatorService: failed to insert final_distribution rows for ' . $month . ': ' . json_encode($this->finalDistributionModel->errors()));
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
