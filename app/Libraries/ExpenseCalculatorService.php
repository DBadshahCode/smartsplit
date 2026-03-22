<?php

namespace App\Libraries;

use App\Models\Expense             as ExpenseModel;
use App\Models\ExpenseType         as ExpenseTypeModel;
use App\Models\ExpenseInvolvement  as ExpenseInvolvementModel;
use App\Models\ChapatiExpense      as ChapatiExpenseModel;
use App\Models\ChapatiAbsence      as ChapatiAbsenceModel;
use App\Models\ChapatiExtraExpense as ChapatiExtraExpenseModel;
use App\Models\ChapatiExtraInvolvement as ChapatiExtraInvolvementModel;
use App\Models\AbsentDay           as AbsentDayModel;
use App\Models\User                as UserModel;
use App\Models\FinalDistribution   as FinalDistributionModel;

class ExpenseCalculatorService
{
    /**
     * Initialise an empty per-user accumulator if it does not exist yet.
     */
    private function initUser(array &$dist, int $uid): void
    {
        if (!isset($dist[$uid])) {
            $dist[$uid] = [
                'chapati_amount'        => 0.0,
                'other_expenses_amount' => 0.0,
                'advance'               => 0.0,
            ];
        }
    }

    /**
     * Safely convert a value that may be a CodeIgniter Time object, a plain
     * string, or null into a Unix timestamp.
     *
     * WHY THIS HELPER EXISTS:
     * CodeIgniter 4 Entity fields cast as 'datetime' return a Time object
     * (which extends PHP's DateTime). PHP's strtotime() only accepts strings —
     * passing a DateTime/Time object causes strtotime() to silently return false,
     * which then casts to 0 in integer arithmetic. This makes every date-range
     * calculation produce totalDays = floor((0 - 0) / 86400) + 1 = 1, regardless
     * of the actual date range. All daysPresent and chapati pro-rata calculations
     * were therefore wrong.
     *
     * Casting to string first calls Time::__toString() which returns the ISO
     * datetime string (e.g. "2026-03-01 00:00:00") that strtotime() can parse.
     */
    private function toTimestamp($date): int
    {
        if ($date === null) {
            return 0;
        }
        $ts = strtotime((string) $date);
        return $ts !== false ? $ts : 0;
    }

    /**
     * Calculate and persist the final distribution for every user in a given month.
     *
     * @param  string $month  Format: YYYY-MM  (e.g. "2026-02")
     * @return array          Raw per-user accumulator keyed by user_id
     */
    public function calculateFinalDistribution(string $month): array
    {
        // ── Model instances ──────────────────────────────────────────────────
        $expenseModel                 = new ExpenseModel();
        $expenseTypeModel             = new ExpenseTypeModel();
        $expenseInvolvementModel      = new ExpenseInvolvementModel();
        $chapatiExpenseModel          = new ChapatiExpenseModel();
        $chapatiAbsenceModel          = new ChapatiAbsenceModel();
        $chapatiExtraExpenseModel     = new ChapatiExtraExpenseModel();
        $chapatiExtraInvolvementModel = new ChapatiExtraInvolvementModel();
        $absentDayModel               = new AbsentDayModel();
        $userModel                    = new UserModel();
        $finalDistributionModel       = new FinalDistributionModel();

        // ── Date range for the requested month ───────────────────────────────
        $startDate = $month . '-01';
        $endDate   = date('Y-m-t', strtotime($startDate));

        // ── Wipe any previously generated rows for this month ────────────────
        $finalDistributionModel->where('month', $month)->delete();

        // ── Accumulator: $dist[user_id] = [chapati, other, advance] ─────────
        $dist = [];

        /*
        |----------------------------------------------------------------------
        | SECTION 1 — OTHER EXPENSES  (split_method: equal | daysPresent)
        |----------------------------------------------------------------------
        | Every expense whose from_date falls within the month is processed.
        | For each expense:
        |   • equal      → each involved user pays an equal share.
        |   • daysPresent→ share is proportional to days present in the period;
        |                   absences come from the `absent_days` table (month-level).
        |   • custom     → logged as a warning; no silent skipping.
        | The payer (paid_by) always gets the full expense amount credited
        | as advance, regardless of split method.
        |----------------------------------------------------------------------
        */
        $expenses = $expenseModel
            ->where('from_date >=', $startDate)
            ->where('from_date <=', $endDate)
            ->findAll();

        // Pre-load all absent_days for the month once (avoids N+1 queries)
        $allAbsentRows = $absentDayModel->where('month', $month)->findAll();
        $absentByUser  = [];
        foreach ($allAbsentRows as $row) {
            $absentByUser[(int) $row->user_id] = (int) $row->days_absent;
        }

        foreach ($expenses as $expense) {

            /** @var \App\Entities\ExpenseType|null $type */
            $type = $expenseTypeModel->find($expense->expense_type_id);
            if (!$type) {
                log_message('warning', "ExpenseCalculatorService: expense #{$expense->id} has unknown type, skipped.");
                continue;
            }

            $involved = $expenseInvolvementModel
                ->where('expense_id', $expense->id)
                ->findAll();

            if (empty($involved)) {
                log_message('warning', "ExpenseCalculatorService: expense #{$expense->id} has no involvement records, skipped.");
                continue;
            }

            $userIds = array_map(fn($i) => (int) $i->user_id, $involved);

            // ── Credit advance to the payer ───────────────────────────────
            if (!empty($expense->paid_by)) {
                $payerId = (int) $expense->paid_by;
                $this->initUser($dist, $payerId);
                $dist[$payerId]['advance'] += (float) $expense->amount;
            }

            // ── Distribute share by split method ─────────────────────────
            switch ($type->split_method) {

                case 'equal':
                    $share = (float) $expense->amount / count($userIds);
                    foreach ($userIds as $uid) {
                        $this->initUser($dist, $uid);
                        $dist[$uid]['other_expenses_amount'] += $share;
                    }
                    break;

                case 'daysPresent':
                    // FIX: use toTimestamp() — $expense->from_date is a CI4 Time
                    // object; plain strtotime() on it returns false → 0.
                    $from      = $this->toTimestamp($expense->from_date);
                    $to        = $this->toTimestamp($expense->to_date);
                    $totalDays = (int) floor(($to - $from) / 86400) + 1;

                    if ($totalDays <= 0) {
                        log_message('warning', "ExpenseCalculatorService: expense #{$expense->id} has zero/negative day range, skipped.");
                        break;
                    }

                    $presentDays    = [];
                    $sumPresentDays = 0;

                    foreach ($userIds as $uid) {
                        $daysAbsent        = $absentByUser[$uid] ?? 0;
                        $days              = max(0, $totalDays - $daysAbsent);
                        $presentDays[$uid] = $days;
                        $sumPresentDays   += $days;
                    }

                    if ($sumPresentDays > 0) {
                        foreach ($presentDays as $uid => $days) {
                            $share = ($days / $sumPresentDays) * (float) $expense->amount;
                            $this->initUser($dist, $uid);
                            $dist[$uid]['other_expenses_amount'] += $share;
                        }
                    } else {
                        log_message('warning', "ExpenseCalculatorService: expense #{$expense->id} — all involved users have 0 present days, share not distributed.");
                    }
                    break;

                case 'custom':
                    log_message('warning', "ExpenseCalculatorService: expense #{$expense->id} uses split_method='custom' which is not yet implemented.");
                    break;

                default:
                    log_message('error', "ExpenseCalculatorService: expense #{$expense->id} has unknown split_method '{$type->split_method}', skipped.");
                    break;
            }
        }

        /*
        |----------------------------------------------------------------------
        | SECTION 2 — CHAPATI EXPENSES
        |----------------------------------------------------------------------
        | Each chapati_expense record covers a date range. All registered users
        | are participants. A user's share is proportional to their days present.
        |
        | Days absent per user are stored in `chapati_absences`, keyed by
        | chapati_expense_id + user_id. A user with NO absence record is treated
        | as fully present (0 absent days).
        |
        | Extra chapati expenses are split equally among their involved users.
        | Neither table has a paid_by column, so no advance is recorded.
        |----------------------------------------------------------------------
        */
        $allUsers   = $userModel->findAll();
        $allUserIds = array_map(fn($u) => (int) $u->id, $allUsers);

        $chapatiExpenses = $chapatiExpenseModel
            ->where('from_date >=', $startDate)
            ->where('from_date <=', $endDate)
            ->findAll();

        foreach ($chapatiExpenses as $chapati) {

            // FIX: same strtotime issue — use toTimestamp() here too.
            $from      = $this->toTimestamp($chapati->from_date);
            $to        = $this->toTimestamp($chapati->to_date);
            $totalDays = (int) floor(($to - $from) / 86400) + 1;

            if ($totalDays <= 0) {
                log_message('warning', "ExpenseCalculatorService: chapati_expense #{$chapati->id} has zero/negative day range, skipped.");
                continue;
            }

            $absenceRows = $chapatiAbsenceModel
                ->where('chapati_expense_id', $chapati->id)
                ->findAll();

            $absenceByUser = [];
            foreach ($absenceRows as $row) {
                $absenceByUser[(int) $row->user_id] = (int) $row->days_absent;
            }

            // ── Base chapati split across ALL users ───────────────────────
            $presentDays    = [];
            $sumPresentDays = 0;

            foreach ($allUserIds as $uid) {
                $daysAbsent        = $absenceByUser[$uid] ?? 0;
                $days              = max(0, $totalDays - $daysAbsent);
                $presentDays[$uid] = $days;
                $sumPresentDays   += $days;
            }

            if ($sumPresentDays > 0) {
                foreach ($presentDays as $uid => $days) {
                    $share = ($days / $sumPresentDays) * (float) $chapati->total_amount;
                    $this->initUser($dist, $uid);
                    $dist[$uid]['chapati_amount'] += $share;
                }
            } else {
                log_message('warning', "ExpenseCalculatorService: chapati_expense #{$chapati->id} — all users have 0 present days, amount not distributed.");
            }

            // ── Extra chapati expenses (equal split among involved users) ─
            $extras = $chapatiExtraExpenseModel
                ->where('chapati_expense_id', $chapati->id)
                ->findAll();

            foreach ($extras as $extra) {

                $involved = $chapatiExtraInvolvementModel
                    ->where('extra_expense_id', $extra->id)
                    ->findAll();

                if (empty($involved)) {
                    log_message('warning', "ExpenseCalculatorService: chapati extra_expense #{$extra->id} has no involvement records, skipped.");
                    continue;
                }

                $extraUserIds = array_map(fn($i) => (int) $i->user_id, $involved);
                $share        = (float) $extra->amount / count($extraUserIds);

                foreach ($extraUserIds as $uid) {
                    $this->initUser($dist, $uid);
                    $dist[$uid]['chapati_amount'] += $share;
                }

                // Note: chapati_extra_expenses has no paid_by column in the
                // current schema, so no advance is recorded here.
                // Uncomment if paid_by is added to the table later:
                //
                // if (!empty($extra->paid_by)) {
                //     $payerId = (int) $extra->paid_by;
                //     $this->initUser($dist, $payerId);
                //     $dist[$payerId]['advance'] += (float) $extra->amount;
                // }
            }
        }

        /*
        |----------------------------------------------------------------------
        | SECTION 3 — PERSIST FINAL DISTRIBUTION
        |----------------------------------------------------------------------
        | For each user in the accumulator:
        |   total_share   = chapati_amount + other_expenses_amount
        |   advance_amount = total amount this user actually paid out
        |   balance        = total_share − advance_amount
        |
        |   balance > 0  → user still owes money    → due_amount = balance
        |   balance <= 0 → user overpaid / in credit → due_amount = 0
        |
        | final_amount stores the signed balance:
        |   positive = user owes this much
        |   negative = user is in credit by this much
        |----------------------------------------------------------------------
        */
        foreach ($dist as $uid => $row) {

            $chapati     = (float) $row['chapati_amount'];
            $other       = (float) $row['other_expenses_amount'];
            $advancePaid = (float) $row['advance'];

            $totalShare = $chapati + $other;
            $balance    = $totalShare - $advancePaid;
            $dueAmount  = $balance > 0 ? $balance : 0.0;

            $insertData = [
                'user_id'               => $uid,
                'month'                 => $month,
                'chapati_amount'        => round($chapati,     0),
                'other_expenses_amount' => round($other,       0),
                'advance_amount'        => round($advancePaid, 0),
                'due_amount'            => round($dueAmount,   0),
                'final_amount'          => round($balance,     0),
                'generated_at'          => date('Y-m-d H:i:s'),
            ];

            if (!$finalDistributionModel->insert($insertData)) {
                log_message('error', "ExpenseCalculatorService: failed to insert final_distribution for user #{$uid}: " . json_encode($finalDistributionModel->errors()));
            }
        }

        return $dist;
    }
}