<?php

namespace App\Controllers;

use \Config\Database as DB;
use App\Controllers\BaseController;
use App\Entities\AbsentDay as AbsentDayEntity;
use App\Entities\ExpenseInvolvement as ExpenseInvolvementEntity;
use App\Entities\FinalDistribution as FinalDistributionEntity;
use App\Entities\User as UserEntity;
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

        return view('finaldistribution/index', $this->viewData([
            'page_title' => $page_title
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
        $latest = $this->finalDistributionModel
            ->select('month')
            ->orderBy('generated_at', 'DESC')
            ->first();

        return $this->response->setJSON([
            'month' => $latest?->month,
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
            ->select([
                'final_distributions.month',
                'final_distributions.expenses_amount',
                'final_distributions.advance_amount',
                'final_distributions.due_amount',
                'final_distributions.final_amount',
                'final_distributions.generated_at',
                'users.name AS user_name',
            ])
            ->join(
                'users',
                'users.id = final_distributions.user_id',
                'left'
            )
            ->where('final_distributions.month', $month)
            ->findAll();

        $data = [];

        foreach ($records as $record) {
            $data[] = [
                'name' => $record->user_name,
                'month' => $record->month,
                'expenses_amount' => $record->expenses_amount,
                'advance_amount' => $record->advance_amount,
                'due_amount' => $record->due_amount,
                'final_amount' => $record->final_amount,
                'generated_at' => $record->generated_at
                    ? (string) $record->generated_at
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
        $users = $this->userModel
            ->select('id, name')
            ->findAll();

        return array_map(
            static fn(UserEntity $user): array => [
                'id' => (int) $user->id,
                'name' => (string) $user->name,
            ],
            $users
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
        $distRows = $this->finalDistributionModel
            ->select([
                'user_id',
                'expenses_amount',
                'advance_amount',
                'final_amount',
            ])
            ->where('month', $month)
            ->findAll();

        $distributions = [];

        foreach ($distRows as $distribution) {
            $userId = (int) $distribution->user_id;

            $distributions[$userId] = [
                'expenses_amount' => $distribution->expenses_amount,
                'advance_amount' => $distribution->advance_amount,
                'final_amount' => $distribution->final_amount,
            ];
        }

        return $distributions;
    }

    /**
     * Build the per-expense breakdown array required by ExcelExportService.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildExpenseDetail(string $month): array
    {
        /**
         * 1. Get all expenses for the billing month.
         *
         * Expense type and paid-by user are fetched using JOINs,
         * so we don't need separate queries for each expense.
         */
        $db = DB::connect();

        $rawExpenses = $db->table('expenses e')
            ->select([
                'e.id',
                'e.amount',
                'e.from_date',
                'e.to_date',
                'e.billing_month',
                'et.name AS expense_type',
                'et.split_method',
                'u.name AS paid_by_name',
            ])
            ->join('expense_types et', 'et.id = e.expense_type_id', 'left')
            ->join('users u', 'u.id = e.paid_by', 'left')
            ->where('e.billing_month', $month)
            ->orderBy('e.id', 'DESC')
            ->get()
            ->getResultArray();

        // Nothing to process.
        if ($rawExpenses === []) {
            return [];
        }

        /**
         * 2. Collect all expense IDs.
         *
         * Example:
         *
         * expenses:
         * 10, 11, 12, 13
         *
         * expenseIds:
         * [10, 11, 12, 13]
         */
        $expenseIds = array_map(
            static fn(array $expense): int => (int) $expense['id'],
            $rawExpenses
        );

        /**
         * 3. Get ALL involvements in ONE query.
         *
         * Before:
         *
         * expense 10 -> query
         * expense 11 -> query
         * expense 12 -> query
         * expense 13 -> query
         *
         * Now:
         *
         * [10, 11, 12, 13] -> ONE query
         */
        $involvements = $this->involvementModel
            ->whereIn('expense_id', $expenseIds)
            ->findAll();

        /**
         * 4. Organize involvements by expense ID.
         *
         * Result will look like:
         *
         * [
         *     10 => [user1, user2, user3],
         *     11 => [user2, user4],
         *     12 => [user1, user3],
         * ]
         */
        $involvementsByExpense = [];

        foreach ($involvements as $involvement) {
            $expenseId = (int) (
                $involvement instanceof ExpenseInvolvementEntity
                ? $involvement->expense_id
                : $involvement['expense_id']
            );

            $involvementsByExpense[$expenseId][] = $involvement;
        }

        /**
         * 5. Build the final result.
         */
        $result = [];

        /**
         * Cache absent days.
         *
         * calculateUserShares() already uses this cache,
         * so we keep that behavior unchanged.
         */
        $absentByExpense = [];

        foreach ($rawExpenses as $expense) {
            $expenseId = (int) $expense['id'];

            $fromDate = substr((string) $expense['from_date'], 0, 10);
            $toDate = substr((string) $expense['to_date'], 0, 10);

            $amount = (float) $expense['amount'];

            $splitMethod = $expense['split_method'] ?? 'equal';

            /**
             * Get involvements from memory instead of querying
             * the database again.
             */
            $expenseInvolvements = $involvementsByExpense[$expenseId] ?? [];

            /**
             * Convert involvement records into user IDs.
             *
             * Example:
             *
             * [
             *     involvement(user_id = 2),
             *     involvement(user_id = 5),
             *     involvement(user_id = 8),
             * ]
             *
             * becomes:
             *
             * [2, 5, 8]
             */
            $involvedUserIds = array_map(
                static fn($involvement): int => (int) (
                    $involvement instanceof ExpenseInvolvementEntity
                    ? $involvement->user_id
                    : $involvement['user_id']
                ),
                $expenseInvolvements
            );

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
                    $absentByExpense
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
            $uid = $row instanceof AbsentDayEntity ? $row->user_id : $row['user_id'];
            $days = $row instanceof AbsentDayEntity ? $row->days_absent : $row['days_absent'];
            $map[(int) $uid] = (int) $days;
        }

        return $map;
    }
}
