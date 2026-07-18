<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\ExcelExportService;
use App\Libraries\ExpenseCalculatorService;
use App\Models\AbsentDay as AbsentDayModel;
use App\Models\ExpenseInvolvement as ExpenseInvolvementModel;
use App\Models\FinalDistribution as FinalDistributionModel;
use App\Models\User as UserModel;

class FinalDistribution extends BaseController
{
    protected ExcelExportService $excelExportService;
    protected ExpenseCalculatorService $expenseCalculatorService;
    protected FinalDistributionModel $finalDistributionModel;
    protected UserModel $userModel;
    protected ExpenseInvolvementModel $involvementModel;
    protected AbsentDayModel $absentDayModel;

    public function __construct()
    {
        $this->excelExportService = new ExcelExportService();
        $this->expenseCalculatorService = new ExpenseCalculatorService();
        $this->finalDistributionModel = new FinalDistributionModel();
        $this->userModel = new UserModel();
        $this->involvementModel = new ExpenseInvolvementModel();
        $this->absentDayModel = new AbsentDayModel();
    }

    public function index()
    {
        $page_title = 'Final Distribution';

        return view('finaldistribution/index', compact('page_title'));
    }

    /**
     * GET /finaldistribution/getLatestMonth
     * No role restriction — any authenticated user can call this.
     *
     * Returns the month whose distribution was generated most recently
     * (by generated_at timestamp), not just the highest month string.
     * Falls back to null if no distributions exist yet.
     */
    public function getLatestMonth()
    {
        $latest = $this->finalDistributionModel
            ->orderBy('generated_at', 'DESC')
            ->first();

        $month = null;
        if ($latest instanceof \App\Entities\FinalDistribution) {
            $month = $latest->month;
        } elseif (is_array($latest) && isset($latest['month'])) {
            $month = $latest['month'];
        }

        return $this->response->setJSON(['month' => $month]);
    }

    public function getDistribution($month)
    {
        $records = $this->finalDistributionModel->where('month', $month)->findAll();

        $data = [];
        foreach ($records as $record) {
            /** @var \App\Entities\User|null $user */
            $user = $this->userModel->find($record->user_id);

            $data[] = [
                'name' => $user instanceof \App\Entities\User ? $user->name : 'Unknown',
                'month' => $record->month,
                'expenses_amount' => $record->other_expenses_amount,
                'advance_amount' => $record->advance_amount,
                'due_amount' => $record->due_amount,
                'final_amount' => $record->final_amount,
                'generated_at' => $record->generated_at ? (string) $record->generated_at : null,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function generateDistribution($month)
    {
        $result = $this->expenseCalculatorService->calculateFinalDistribution($month);

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

        // Critical on shared hosting — clears any stray output before streaming.
        if (ob_get_level()) {
            ob_end_clean();
        }

        try {
            $users = $this->buildUserList();
            $distributions = $this->buildDistributionMap($month);
            $expenses = $this->buildExpenseDetail($month, $users);

            $data = [
                'users' => $users,
                'distributions' => $distributions,
                'expenses' => $expenses,
            ];

            $tmpPath = $this->excelExportService->generate($month, $data);

            // Verify the file was actually written before streaming.
            if (!file_exists($tmpPath) || filesize($tmpPath) === 0) {
                throw new \RuntimeException('Excel file was not created at: ' . $tmpPath);
            }

            $filename = 'SmartSplit_' . $month . '_Distribution.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            header('Content-Length: ' . filesize($tmpPath));

            readfile($tmpPath);
            @unlink($tmpPath); // Clean up temp file after streaming.
            exit;
        } catch (\Throwable $e) {
            log_message('error', '[exportExcel] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

            if (ob_get_level()) {
                ob_end_clean();
            }

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
     * @return array<int, array{id: int, name: string}>
     */
    private function buildUserList(): array
    {
        $allUsers = $this->userModel->findAll();

        $users = [];
        foreach ($allUsers as $u) {
            $users[] = [
                'id' => $u instanceof \App\Entities\User ? $u->id : $u['id'],
                'name' => $u instanceof \App\Entities\User ? $u->name : $u['name'],
            ];
        }

        return $users;
    }

    /**
     * @return array<int, array{other_expenses_amount: float, advance_amount: float, final_amount: float}>
     */
    private function buildDistributionMap(string $month): array
    {
        $distRows = $this->finalDistributionModel->where('month', $month)->findAll();

        $distributions = [];
        foreach ($distRows as $dr) {
            $uid = $dr instanceof \App\Entities\FinalDistribution ? $dr->user_id : $dr['user_id'];

            $distributions[$uid] = [
                'other_expenses_amount' => $dr instanceof \App\Entities\FinalDistribution ? $dr->other_expenses_amount : $dr['other_expenses_amount'],
                'advance_amount' => $dr instanceof \App\Entities\FinalDistribution ? $dr->advance_amount : $dr['advance_amount'],
                'final_amount' => $dr instanceof \App\Entities\FinalDistribution ? $dr->final_amount : $dr['final_amount'],
            ];
        }

        return $distributions;
    }

    /**
     * Build the per-expense breakdown array that ExcelExportService expects.
     *
     * absent_days is keyed by expense_id + user_id and is lazy-loaded per
     * expense — the same pattern used in ExpenseCalculatorService.
     *
     * Each returned entry:
     *   id, expense_type, from_date, to_date, amount, split_method,
     *   paid_by_name, billing_month, user_shares[user_id => amount]
     *
     * @param  array<int, array{id: int, name: string}> $users
     * @return array<int, array<string, mixed>>
     */
    private function buildExpenseDetail(string $month, array $users): array
    {
        [$year, $mo] = explode('-', $month);
        $start = $month . '-01';
        $end = date('Y-m-t', mktime(0, 0, 0, (int) $mo, 1, (int) $year));

        // Single joined query — includes split_method directly.
        $db = \Config\Database::connect();
        $rawExpenses = $db->table('expenses e')
            ->select('e.id, e.amount, e.from_date, e.to_date, e.billing_month,
                      et.name AS expense_type, et.split_method,
                      u.name  AS paid_by_name')
            ->join('expense_types et', 'et.id = e.expense_type_id', 'left')
            ->join('users u', 'u.id  = e.paid_by', 'left')
            ->orderBy('e.id', 'DESC')
            ->get()
            ->getResultArray();

        // Absent days — loaded per expense (lazy cache).
        $absentByExpense = []; // [expense_id => [user_id => days_absent]]

        $result = [];

        foreach ($rawExpenses as $exp) {
            // Date strings — safe against CI4 Time objects.
            $fromStr = substr((string) $exp['from_date'], 0, 10);
            $toStr = substr((string) $exp['to_date'], 0, 10);

            // Only expenses that overlap with this month.
            if ($toStr < $start || $fromStr > $end) {
                continue;
            }

            $expId = (int) $exp['id'];
            $amount = (float) $exp['amount'];
            $splitMethod = $exp['split_method'] ?? 'equal';

            $involvements = $this->involvementModel->where('expense_id', $expId)->findAll();
            $involvedIds = array_map(
                fn ($i) => (int) ($i instanceof \App\Entities\ExpenseInvolvement ? $i->user_id : $i['user_id']),
                $involvements
            );
            $involvedCount = count($involvedIds);

            $userShares = $involvedCount > 0
                ? $this->calculateUserShares($splitMethod, $amount, $involvedIds, $expId, $fromStr, $toStr, $absentByExpense)
                : [];

            $result[] = [
                'id' => $expId,
                'expense_type' => $exp['expense_type'] ?? '',
                'from_date' => $fromStr,
                'to_date' => $toStr,
                'amount' => $amount,
                'split_method' => $splitMethod,
                'paid_by_name' => $exp['paid_by_name'] ?? '—',
                'billing_month' => $exp['billing_month'] ?? $month,
                'user_shares' => $userShares,
            ];
        }

        return $result;
    }

    /**
     * @param  int[] $involvedIds
     * @param  array<int, array<int, int>> $absentByExpense Lazy cache, passed by reference.
     * @return array<int, float> [user_id => share]
     */
    private function calculateUserShares(
        string $splitMethod,
        float $amount,
        array $involvedIds,
        int $expId,
        string $fromStr,
        string $toStr,
        array &$absentByExpense
    ): array {
        $involvedCount = count($involvedIds);

        if ($splitMethod === 'daysPresent') {
            // Dates are already plain strings — strtotime() is safe here.
            $totalDays = (int) ((strtotime($toStr) - strtotime($fromStr)) / 86400) + 1;

            if (!isset($absentByExpense[$expId])) {
                $absentByExpense[$expId] = $this->loadAbsentDays($expId);
            }
            $absentMap = $absentByExpense[$expId];

            $presentDays = [];
            $totalPresent = 0;
            foreach ($involvedIds as $uid) {
                $absent = $absentMap[$uid] ?? 0;
                $present = max(0, $totalDays - $absent);
                $presentDays[$uid] = $present;
                $totalPresent += $present;
            }

            $userShares = [];
            foreach ($involvedIds as $uid) {
                $userShares[$uid] = $totalPresent > 0
                    ? round($amount * ($presentDays[$uid] / $totalPresent), 2)
                    : 0.0;
            }

            return $userShares;
        }

        if ($splitMethod !== 'equal') {
            // custom — not implemented; fall back to equal.
            log_message('warning', "buildExpenseDetail: split_method '{$splitMethod}' on expense {$expId}, falling back to equal.");
        }

        $share = round($amount / $involvedCount, 2);
        $userShares = [];
        foreach ($involvedIds as $uid) {
            $userShares[$uid] = $share;
        }

        return $userShares;
    }

    /**
     * @return array<int, int> [user_id => days_absent]
     */
    private function loadAbsentDays(int $expenseId): array
    {
        $rows = $this->absentDayModel->where('expense_id', $expenseId)->findAll();

        $map = [];
        foreach ($rows as $row) {
            $uid = $row instanceof \App\Entities\AbsentDay ? $row->user_id : $row['user_id'];
            $days = $row instanceof \App\Entities\AbsentDay ? $row->days_absent : $row['days_absent'];
            $map[(int) $uid] = (int) $days;
        }

        return $map;
    }
}