<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\ExcelExportService;
use App\Libraries\ExpenseCalculatorService;
use App\Models\AbsentDay as AbsentDayModel;
use App\Models\Expense as ExpenseModel;
use App\Models\ExpenseInvolvement as ExpenseInvolvementModel;
use App\Models\FinalDistribution as FinalDistributionModel;
use App\Models\User as UserModel;

class FinalDistribution extends BaseController
{
    protected ExcelExportService $excelExportService;
    protected ExpenseCalculatorService $expenseCalculatorService;
    protected FinalDistributionModel $finalDistributionModel;
    protected UserModel $userModel;
    protected ExpenseModel $expenseModel;
    protected ExpenseInvolvementModel $involvementModel;
    protected AbsentDayModel $absentDayModel;

    public function __construct()
    {
        $this->excelExportService = new ExcelExportService();
        $this->expenseCalculatorService = new ExpenseCalculatorService();
        $this->finalDistributionModel = new FinalDistributionModel();
        $this->userModel = new UserModel();
        $this->expenseModel = new ExpenseModel();
        $this->involvementModel = new ExpenseInvolvementModel();
        $this->absentDayModel = new AbsentDayModel();
    }

    public function index()
    {
        return view('finaldistribution/index', $this->viewData([
            'pageTitle' => 'Final Distribution'
        ]));
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
        return $this->response->setJSON([
            'month' => $this->finalDistributionModel->getLatestGeneratedMonth(),
        ]);
    }

    /**
     * Get final distribution for a billing month.
     *
     * @param string $month Billing month in YYYY-MM format.
     */
    public function getDistribution(string $month)
    {
        $records = $this->finalDistributionModel
            ->getMonthWithUserNames($month);

        $data = [];

        foreach ($records as $record) {
            $data[] = [
                'name' => $record['user_name'],
                'month' => $record['month'],
                'expenses_amount' => $record['expenses_amount'],
                'advance_amount' => $record['advance_amount'],
                'due_amount' => $record['due_amount'],
                'final_amount' => $record['final_amount'],
                'generated_at' => $record['generated_at'] !== null
                    ? (string) $record['generated_at']
                    : null,
            ];
        }

        return $this->response->setJSON([
            'data' => $data,
        ]);
    }

    public function generateDistribution(string $month)
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
            $expenses = $this->buildExpenseDetail($month);

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
     * Build user list required by ExcelExportService.
     *
     * @return array<int, array{id: int, name: string}>
     */
    private function buildUserList(): array
    {
        return array_map(
            static fn(array $user): array => [
                'id' => (int) $user['id'],
                'name' => (string) $user['name'],
            ],
            $this->userModel->getIdNameList()
        );
    }

    /**
     * Build distribution lookup map for ExcelExportService.
     *
     * @return array<int, array{
     *     expenses_amount: float,
     *     advance_amount: float,
     *     final_amount: float
     * }>
     */
    private function buildDistributionMap(string $month): array
    {
        return $this->finalDistributionModel->getAmountsMapForMonth($month);
    }

    /**
     * Build the per-expense breakdown array required by ExcelExportService.
     *
     * The whole month is read in three queries — expenses joined to their type
     * and payer, every involvement, every absence — and the split maths then
     * runs in memory. Previously the absence rows were fetched one query per
     * daysPresent expense.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildExpenseDetail(string $month): array
    {
        $rawExpenses = $this->expenseModel->getExpensesForMonth($month);

        // Nothing to process.
        if ($rawExpenses === []) {
            return [];
        }

        $expenseIds = array_map(
            static fn(array $expense): int => (int) $expense['id'],
            $rawExpenses
        );

        /**
         * All involvements for the month in ONE query, keyed by expense.
         *
         * Before:  expense 10 -> query, 11 -> query, 12 -> query …
         * Now:     [10, 11, 12, 13] -> ONE query
         */
        $involvementsByExpense = $this->involvementModel
            ->getUserIdsByExpenseIds($expenseIds);

        /**
         * Absences are only read for daysPresent expenses, so only those IDs
         * are queried — an equal-split month issues no absence query at all.
         */
        $daysPresentIds = [];

        foreach ($rawExpenses as $expense) {
            if (($expense['split_method'] ?? 'equal') === 'daysPresent') {
                $daysPresentIds[] = (int) $expense['id'];
            }
        }

        $absentByExpense = $this->absentDayModel
            ->getDaysByExpenseIds($daysPresentIds);

        $result = [];

        foreach ($rawExpenses as $expense) {
            $expenseId = (int) $expense['id'];

            $fromDate = substr((string) $expense['from_date'], 0, 10);
            $toDate = substr((string) $expense['to_date'], 0, 10);

            $amount = (float) $expense['amount'];

            $splitMethod = $expense['split_method'] ?? 'equal';

            $involvedUserIds = $involvementsByExpense[$expenseId] ?? [];

            /**
             * Calculate each user's share.
             */
            $userShares = $involvedUserIds !== []
                ? $this->calculateUserShares(
                    $splitMethod,
                    $amount,
                    $involvedUserIds,
                    $expenseId,
                    $fromDate,
                    $toDate,
                    $absentByExpense[$expenseId] ?? []
                )
                : [];

            /**
             * Prepare data required by ExcelExportService.
             */
            $result[] = [
                'id' => $expenseId,
                'expense_type' => $expense['expense_type'] ?? '',
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'amount' => $amount,
                'split_method' => $splitMethod,
                'paid_by_name' => $expense['paid_by_name'] ?? '—',
                'billing_month' => $expense['billing_month'] ?? $month,
                'user_shares' => $userShares,
            ];
        }

        return $result;
    }

    /**
     * @param  int[] $involvedIds
     * @param  array<int, int> $absentMap [user_id => days_absent], prefetched.
     * @return array<int, float> [user_id => share]
     */
    private function calculateUserShares(
        string $splitMethod,
        float $amount,
        array $involvedIds,
        int $expId,
        string $fromStr,
        string $toStr,
        array $absentMap
    ): array {
        $involvedCount = count($involvedIds);

        if ($splitMethod === 'daysPresent') {
            // Dates are already plain strings — strtotime() is safe here.
            $totalDays = (int) ((strtotime($toStr) - strtotime($fromStr)) / 86400) + 1;

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
}
