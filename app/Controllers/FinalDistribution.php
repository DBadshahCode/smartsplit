<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\ExpenseCalculatorService;
use App\Models\FinalDistribution as FinalDistributionModel;
use App\Libraries\ExcelExportService as ExcelExportService;
use App\Models\User as UserModel;
use App\Models\Expense as ExpenseModel;
use App\Models\ExpenseType as ExpenseTypeModel;
use App\Models\ExpenseInvolvement as ExpenseInvolvementModel;
use App\Models\ChapatiExpense as ChapatiExpenseModel;
use App\Models\ChapatiAbsence as ChapatiAbsenceModel;
use App\Models\ChapatiExtraExpense as ChapatiExtraExpenseModel;
use App\Models\ChapatiExtraInvolvement as ChapatiExtraInvolvementModel;

class FinalDistribution extends BaseController
{
    public function index()
    {
        $page_title = 'Final Distribution';
        return view('finaldistribution/index', compact('page_title'));
    }

    public function getDistribution($month)
    {
        $finalDistributionModel = new FinalDistributionModel();
        $userModel = new UserModel();

        $records = $finalDistributionModel->where('month', $month)->findAll();

        $data = [];
        foreach ($records as $record) {
            /** @var \App\Entities\User|null $user */
            $user = $userModel->find($record->user_id);
            $data[] = [
                'name' => $user ? $user->name : 'Unknown',
                'month' => $record->month,
                'chapati_amount' => $record->chapati_amount,
                'expenses_amount' => $record->other_expenses_amount,
                'advance_amount' => $record->advance_amount,
                'due_amount' => $record->due_amount,
                'final_amount' => $record->final_amount,
                'generated_at'    => $record->generated_at ? (string) $record->generated_at : null,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function generateDistribution($month)
    {
        $service = new ExpenseCalculatorService();
        $result = $service->calculateFinalDistribution($month);
        return $this->response->setJSON(['status' => 'success', 'data' => $result]);
    }

    /**
     * GET /finaldistribution/exportExcel/:month
     * Admin-only (enforced by AdminFilter on route group).
     *
     * Streams a formatted .xlsx workbook for the requested month.
     */
    public function exportExcel(string $month): void
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $this->response->setStatusCode(400)->setBody('Invalid month format.')->send();
            return;
        }

        // ✅ Critical on shared hosting — clears any stray output before streaming
        if (ob_get_level())
            ob_end_clean();

        try {
            $userModel = new UserModel();
            $involvementModel = new ExpenseInvolvementModel();
            $chapatiModel = new ChapatiExpenseModel();
            $chapatiAbsenceModel = new ChapatiAbsenceModel();
            $chapatiExtraModel = new ChapatiExtraExpenseModel();
            $chapatiExtraInvModel = new ChapatiExtraInvolvementModel();
            $distModel = new FinalDistributionModel();

            $allUsers = $userModel->findAll();
            $users = [];
            foreach ($allUsers as $u) {
                $users[] = [
                    'id' => $u instanceof \App\Entities\User ? $u->id : $u['id'],
                    'name' => $u instanceof \App\Entities\User ? $u->name : $u['name'],
                ];
            }

            $distRows = $distModel->where('month', $month)->findAll();
            $distributions = [];
            foreach ($distRows as $dr) {
                $uid = $dr instanceof \App\Entities\FinalDistribution ? $dr->user_id : $dr['user_id'];
                $distributions[$uid] = [
                    'chapati_amount' => $dr instanceof \App\Entities\FinalDistribution ? $dr->chapati_amount : $dr['chapati_amount'],
                    'other_expenses_amount' => $dr instanceof \App\Entities\FinalDistribution ? $dr->other_expenses_amount : $dr['other_expenses_amount'],
                    'advance_amount' => $dr instanceof \App\Entities\FinalDistribution ? $dr->advance_amount : $dr['advance_amount'],
                    'final_amount' => $dr instanceof \App\Entities\FinalDistribution ? $dr->final_amount : $dr['final_amount'],
                ];
            }

            $expenses = $this->buildExpenseDetail($month, $users, $involvementModel);
            $chapatiExpenses = $this->buildChapatiDetail($month, $users, $chapatiModel, $chapatiAbsenceModel, $chapatiExtraModel, $chapatiExtraInvModel);

            $data = [
                'users' => $users,
                'distributions' => $distributions,
                'expenses' => $expenses,
                'chapati_expenses' => $chapatiExpenses,
            ];

            $svc = new ExcelExportService();
            $tmpPath = $svc->generate($month, $data);

            // ✅ Verify file was actually written before streaming
            if (!file_exists($tmpPath) || filesize($tmpPath) === 0) {
                throw new \RuntimeException('Excel file was not created at: ' . $tmpPath);
            }

            $filename = 'SmartSplit_' . $month . '_Distribution.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            header('Content-Length: ' . filesize($tmpPath));

            readfile($tmpPath);
            @unlink($tmpPath);   // clean up temp file after streaming
            exit;

        } catch (\Throwable $e) {
            log_message('error', '[exportExcel] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

            if (ob_get_level())
                ob_end_clean();
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            exit;
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Build the per-expense breakdown array that ExcelExportService expects.
     *
     * absent_days is now keyed by expense_id + user_id (mirrors chapati_absences).
     * We lazy-load per expense inside the loop — exactly the same pattern used
     * in ExpenseCalculatorService Section 1 after the schema change.
     *
     * Unused parameters $expenseModel, $expenseTypeModel, $calcResult removed —
     * the joined query below fetches everything we need in one go.
     *
     * Each returned entry:
     *   id, expense_type, from_date, to_date, amount, split_method,
     *   paid_by_name, user_shares[user_id => amount]
     */
    private function buildExpenseDetail(
        string $month,
        array $users,
        $involvementModel
    ): array {
        [$year, $mo] = explode('-', $month);
        $start = $month . '-01';
        $end = date('Y-m-t', mktime(0, 0, 0, (int) $mo, 1, (int) $year));

        // ── Single joined query — includes split_method directly ──────────────
        $db = \Config\Database::connect();
        $rawExpenses = $db->table('expenses e')
            ->select('e.id, e.amount, e.from_date, e.to_date,
                      et.name AS expense_type, et.split_method,
                      u.name  AS paid_by_name')
            ->join('expense_types et', 'et.id = e.expense_type_id', 'left')
            ->join('users u', 'u.id  = e.paid_by', 'left')
            ->orderBy('e.id', 'DESC')
            ->get()
            ->getResultArray();

        // ── Absent days model — loaded per expense (lazy cache) ───────────────
        $absentDayModel = new \App\Models\AbsentDay();
        $absentByExpense = [];   // [expense_id => [user_id => days_absent]]

        $result = [];

        foreach ($rawExpenses as $exp) {
            // ── Date strings — safe against CI4 Time objects ──────────────────
            $fromStr = substr((string) $exp['from_date'], 0, 10);
            $toStr = substr((string) $exp['to_date'], 0, 10);

            // ── Filter: only expenses that overlap with this month ─────────────
            if ($toStr < $start || $fromStr > $end) {
                continue;
            }

            $expId = (int) $exp['id'];
            $amount = (float) $exp['amount'];
            $splitMethod = $exp['split_method'] ?? 'equal';

            // ── Load involved users for this expense ──────────────────────────
            $involvements = $involvementModel->where('expense_id', $expId)->findAll();
            $involvedIds = array_map(
                fn($i) => (int) ($i instanceof \App\Entities\ExpenseInvolvement ? $i->user_id : $i['user_id']),
                $involvements
            );
            $involvedCount = count($involvedIds);

            // ── Calculate user shares ─────────────────────────────────────────
            $userShares = [];

            if ($involvedCount > 0) {

                if ($splitMethod === 'equal') {
                    $share = $amount / $involvedCount;
                    foreach ($involvedIds as $uid) {
                        $userShares[$uid] = round($share, 2);
                    }

                } elseif ($splitMethod === 'daysPresent') {
                    // Dates are already plain strings — strtotime() is safe here.
                    $totalDays = (int) ((strtotime($toStr) - strtotime($fromStr)) / 86400) + 1;

                    // Lazy-load absent_days for this expense (per-expense_id,
                    // matching the new schema and ExpenseCalculatorService logic).
                    if (!isset($absentByExpense[$expId])) {
                        $absentRows = $absentDayModel->where('expense_id', $expId)->findAll();
                        $map = [];
                        foreach ($absentRows as $ar) {
                            $uid = $ar instanceof \App\Entities\AbsentDay ? $ar->user_id : $ar['user_id'];
                            $days = $ar instanceof \App\Entities\AbsentDay ? $ar->days_absent : $ar['days_absent'];
                            $map[(int) $uid] = (int) $days;
                        }
                        $absentByExpense[$expId] = $map;
                    }

                    $presentDays = [];
                    $totalPresent = 0;
                    foreach ($involvedIds as $uid) {
                        $absent = $absentByExpense[$expId][$uid] ?? 0;
                        $present = max(0, $totalDays - $absent);
                        $presentDays[$uid] = $present;
                        $totalPresent += $present;
                    }

                    foreach ($involvedIds as $uid) {
                        $userShares[$uid] = $totalPresent > 0
                            ? round($amount * ($presentDays[$uid] / $totalPresent), 2)
                            : 0;
                    }

                } else {
                    // custom — not implemented; fall back to equal
                    log_message('warning', "buildExpenseDetail: split_method 'custom' on expense {$expId}, falling back to equal.");
                    $share = $amount / $involvedCount;
                    foreach ($involvedIds as $uid) {
                        $userShares[$uid] = round($share, 2);
                    }
                }
            }

            $result[] = [
                'id' => $expId,
                'expense_type' => $exp['expense_type'] ?? '',
                'from_date' => $fromStr,
                'to_date' => $toStr,
                'amount' => $amount,
                'split_method' => $splitMethod,
                'paid_by_name' => $exp['paid_by_name'] ?? '—',
                'user_shares' => $userShares,
            ];
        }

        return $result;
    }

    /**
     * Build the per-chapati-expense breakdown that ExcelExportService expects.
     * Unchanged from original — chapati_absences schema was not modified.
     *
     * Each returned entry:
     *   id, expense_type, from_date, to_date, total_amount, total_days,
     *   user_days[user_id => days_present],
     *   base_shares[user_id => amount],
     *   extras[{description, amount, involved_names, user_shares[user_id => amount]}]
     */
    private function buildChapatiDetail(
        string $month,
        array $users,
        $chapatiModel,
        $chapatiAbsenceModel,
        $chapatiExtraModel,
        $chapatiExtraInvModel
    ): array {
        [$year, $mo] = explode('-', $month);
        $start = $month . '-01';
        $end = date('Y-m-t', mktime(0, 0, 0, (int) $mo, 1, (int) $year));

        $chapatiRows = $chapatiModel->findAll();
        $result = [];

        foreach ($chapatiRows as $cr) {
            // ── Entity-safe property access ───────────────────────────────────
            if ($cr instanceof \App\Entities\ChapatiExpense) {
                $fromDate = (string) $cr->from_date;
                $toDate = (string) $cr->to_date;
                $cId = $cr->id;
                $totalAmt = (float) $cr->total_amount;
            } else {
                $fromDate = $cr['from_date'] ?? '';
                $toDate = $cr['to_date'] ?? '';
                $cId = $cr['id'];
                $totalAmt = (float) ($cr['total_amount'] ?? 0);
            }

            $fromStr = substr($fromDate, 0, 10);
            $toStr = substr($toDate, 0, 10);

            // ── Filter to month ───────────────────────────────────────────────
            if ($toStr < $start || $fromStr > $end) {
                continue;
            }

            $totalDays = (int) ((strtotime($toStr) - strtotime($fromStr)) / 86400) + 1;

            // ── Absences for this chapati record ──────────────────────────────
            $absenceRows = $chapatiAbsenceModel->where('chapati_expense_id', $cId)->findAll();
            $absenceMap = [];
            foreach ($absenceRows as $ab) {
                $abUid = $ab instanceof \App\Entities\ChapatiAbsence ? $ab->user_id : $ab['user_id'];
                $abDays = $ab instanceof \App\Entities\ChapatiAbsence ? $ab->days_absent : $ab['days_absent'];
                $absenceMap[$abUid] = (int) $abDays;
            }

            // ── Per-user days present + proportional base share ───────────────
            $totalPresentDays = 0;
            $userDays = [];
            foreach ($users as $u) {
                $uid = $u['id'];
                $absent = $absenceMap[$uid] ?? 0;
                $present = max(0, $totalDays - $absent);
                $userDays[$uid] = $present;
                $totalPresentDays += $present;
            }

            $baseShares = [];
            foreach ($users as $u) {
                $uid = $u['id'];
                $baseShares[$uid] = $totalPresentDays > 0
                    ? round($totalAmt * ($userDays[$uid] / $totalPresentDays), 2)
                    : 0;
            }

            // ── Extra expenses ────────────────────────────────────────────────
            $extraRows = $chapatiExtraModel->where('chapati_expense_id', $cId)->findAll();
            $extras = [];
            foreach ($extraRows as $er) {
                $extraId = $er instanceof \App\Entities\ChapatiExtraExpense ? $er->id : $er['id'];
                $extraAmt = $er instanceof \App\Entities\ChapatiExtraExpense ? $er->amount : $er['amount'];

                $invRows = $chapatiExtraInvModel->where('extra_expense_id', $extraId)->findAll();
                $invIds = array_map(
                    fn($i) => (int) ($i instanceof \App\Entities\ChapatiExtraInvolvement ? $i->user_id : $i['user_id']),
                    $invRows
                );
                $invCount = count($invIds);

                $invNames = [];
                foreach ($users as $u) {
                    if (in_array($u['id'], $invIds)) {
                        $invNames[] = $u['name'];
                    }
                }

                $extraUserShares = [];
                if ($invCount > 0) {
                    $share = round((float) $extraAmt / $invCount, 2);
                    foreach ($invIds as $uid) {
                        $extraUserShares[$uid] = $share;
                    }
                }

                $extras[] = [
                    'description' => 'Extra #' . $extraId,
                    'amount' => (float) $extraAmt,
                    'involved_names' => implode(', ', $invNames),
                    'user_shares' => $extraUserShares,
                ];
            }

            $result[] = [
                'id' => $cId,
                'expense_type' => 'Chapati',
                'from_date' => $fromStr,
                'to_date' => $toStr,
                'total_amount' => $totalAmt,
                'total_days' => $totalDays,
                'user_days' => $userDays,
                'base_shares' => $baseShares,
                'extras' => $extras,
            ];
        }

        return $result;
    }
}