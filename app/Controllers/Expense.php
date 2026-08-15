<?php

namespace App\Controllers;

use \Config\Database as DB;
use App\Controllers\BaseController;
use App\Entities\Expense as ExpenseEntity;
use App\Models\AbsentDay as AbsentDayModel;
use App\Models\Expense as ExpenseModel;
use App\Models\ExpenseInvolvement as ExpenseInvolvementModel;
use App\Models\ExpenseType as ExpenseTypeModel;
use App\Models\User as UserModel;

class Expense extends BaseController
{
    protected ExpenseModel $expenseModel;
    protected ExpenseInvolvementModel $expenseInvolvementModel;
    protected ExpenseTypeModel $expenseTypeModel;
    protected UserModel $userModel;
    protected AbsentDayModel $absentDayModel;
    public function __construct()
    {
        $this->expenseModel = new ExpenseModel();
        $this->expenseInvolvementModel = new ExpenseInvolvementModel();
        $this->expenseTypeModel = new ExpenseTypeModel();
        $this->userModel = new UserModel();
        $this->absentDayModel = new AbsentDayModel();
    }
    public function index()
    {
        $page_title = 'Expense Management';

        $expenseTypes = $this->expenseTypeModel
            ->select('id, name, split_method')
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();

        $users = $this->userModel
            ->select('id, name')
            ->orderBy('name', 'ASC')
            ->findAll();

        return view('expense/index', $this->viewData([
            'page_title' => $page_title,
            'expenseTypes' => $expenseTypes,
            'users' => $users,
        ]));
    }

    public function getExpenses()
    {
        $expenses = $this->expenseModel
            ->select([
                'expenses.id',
                'expenses.description',
                'expenses.amount',
                'expenses.billing_month',
                'expenses.from_date',
                'expenses.to_date',
                'expense_types.name AS expense_type',
                'users.name AS paid_by_name',
                'COUNT(DISTINCT expense_involvements.user_id) AS total_involved',
                'GROUP_CONCAT(
                DISTINCT involved_users.name
                ORDER BY involved_users.name
                SEPARATOR ", "
            ) AS involved_names',
            ])
            ->join(
                'expense_types',
                'expense_types.id = expenses.expense_type_id',
                'left'
            )
            ->join(
                'users',
                'users.id = expenses.paid_by',
                'left'
            )
            ->join(
                'expense_involvements',
                'expense_involvements.expense_id = expenses.id',
                'left'
            )
            ->join(
                'users AS involved_users',
                'involved_users.id = expense_involvements.user_id',
                'left'
            )
            ->groupBy('expenses.id')
            ->orderBy('expenses.id', 'DESC')
            ->findAll();

        return $this->response->setJSON([
            'data' => $expenses,
        ]);
    }

    public function addExpense()
    {
        $data = $this->request->getPost();

        $involvedUsers = $data['involved_users'] ?? [];

        if (!is_array($involvedUsers) || $involvedUsers === []) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'error' => 'At least one involved user is required.',
                ]);
        }

        $paidBy = $data['paid_by'] ?: null;

        $db = DB::connect();

        $db->transStart();

        $expenseId = $this->expenseModel->insert([
            'expense_type_id' => $data['expense_type_id'],
            'description' => $data['description'],
            'amount' => $data['amount'],
            'billing_month' => $data['billing_month'],
            'from_date' => $data['from_date'],
            'to_date' => $data['to_date'],
            'paid_by' => $paidBy,
        ]);

        /**
         * Prepare all involvement rows first.
         */
        $involvementRows = [];

        foreach (array_unique($involvedUsers) as $userId) {
            $involvementRows[] = [
                'expense_id' => $expenseId,
                'user_id' => (int) $userId,
            ];
        }

        /**
         * One bulk INSERT instead of one INSERT per user.
         */
        if ($involvementRows !== []) {
            $this->expenseInvolvementModel->insertBatch($involvementRows);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'error' => 'Failed to create expense.',
                ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'id' => $expenseId,
        ]);
    }

    public function deleteExpense(int $id)
    {
        $db = DB::connect();

        $db->transStart();

        $this->absentDayModel
            ->where('expense_id', $id)
            ->delete();

        $this->expenseInvolvementModel
            ->where('expense_id', $id)
            ->delete();

        $this->expenseModel->delete($id);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'error' => 'Failed to delete expense.',
                ]);
        }

        return $this->response->setJSON([
            'status' => 'deleted',
        ]);
    }

    public function bulkDeleteExpenses()
    {
        if ((string) $this->currentUser['role'] !== 'admin') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Admin access required']);
        }

        $ids = $this->request->getPost('ids') ?: [];

        if (!is_array($ids) || empty($ids)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'No expenses selected']);
        }

        // Sanitize: keep only positive integer IDs
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), function ($id) {
            return $id > 0;
        })));

        if (empty($ids)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid expense IDs']);
        }

        $db = DB::connect();
        $db->transStart();

        $this->absentDayModel->whereIn('expense_id', $ids)->delete();
        $this->expenseInvolvementModel->whereIn('expense_id', $ids)->delete();
        $this->expenseModel->whereIn('id', $ids)->delete();

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to delete selected expenses']);
        }

        return $this->response->setJSON([
            'status' => 'deleted',
            'deleted' => count($ids),
        ]);
    }

    public function getExpense(int $id)
    {
        /** @var ExpenseEntity|null $expense */
        $expense = $this->expenseModel->find($id);
        if (!($expense instanceof ExpenseEntity)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        }

        // Collect involved user IDs for this expense
        $rows = $this->expenseInvolvementModel
            ->select('user_id')
            ->where('expense_id', $id)
            ->findAll();
        $involvedIds = array_map(
            static fn($row): int => (int) $row->user_id,
            $rows
        );

        $canEdit = $this->canEditExpense($expense, $this->currentUser['role'], $this->currentUser['id']);

        return $this->response->setJSON([
            'data' => [
                'id' => (int) $expense->id,
                'expense_type_id' => (int) $expense->expense_type_id,
                'description' => $expense->description,
                'amount' => $expense->amount,
                'billing_month' => $expense->billing_month,
                'from_date' => (string) $expense->from_date,
                'to_date' => (string) $expense->to_date,
                'paid_by' => $expense->paid_by,
                'involved_ids' => $involvedIds,
                'can_edit' => $canEdit,
            ],
        ]);
    }

    public function updateExpense(int $id)
    {
        /** @var ExpenseEntity|null $expense */
        $expense = $this->expenseModel->find($id);

        if (!($expense instanceof ExpenseEntity)) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'error' => 'Not found',
                ]);
        }

        $currentUserId = (int) $this->currentUser['id'];
        $currentUserRole = (string) $this->currentUser['role'];

        if (!$this->canEditExpense(
            $expense,
            $currentUserRole,
            $currentUserId
        )) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'error' => 'You do not have permission to edit this expense',
                ]);
        }

        $data = $this->request->getPost();

        $involvedUsers = $data['involved_users'] ?? [];

        if (!is_array($involvedUsers) || $involvedUsers === []) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'error' => 'At least one involved user is required.',
                ]);
        }

        $paidBy = $data['paid_by'] ?: null;

        $db = DB::connect();

        $db->transStart();

        /**
         * Update the expense.
         */
        $this->expenseModel->update($id, [
            'expense_type_id' => $data['expense_type_id'],
            'description' => $data['description'],
            'amount' => $data['amount'],
            'billing_month' => $data['billing_month'],
            'from_date' => $data['from_date'],
            'to_date' => $data['to_date'],
            'paid_by' => $paidBy,
        ]);

        /**
         * Replace existing involvements.
         */
        $this->expenseInvolvementModel
            ->where('expense_id', $id)
            ->delete();

        /**
         * Prepare new involvement rows.
         */
        $involvementRows = [];

        foreach (array_unique($involvedUsers) as $userId) {
            $involvementRows[] = [
                'expense_id' => $id,
                'user_id' => (int) $userId,
            ];
        }

        /**
         * Insert all involvement records in one operation.
         */
        if ($involvementRows !== []) {
            $this->expenseInvolvementModel
                ->insertBatch($involvementRows);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'error' => 'Failed to update expense.',
                ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
        ]);
    }

    /**
     * Determine if the current user can edit this expense
     * Rules:
     * - Admins can always edit
     * - Non-admins can only edit if they are the paid_by user
     * - Non-admins can edit if paid_by is null/empty (not assigned)
     */
    private function canEditExpense(
        ExpenseEntity $expense,
        string $role,
        int $currentUserId
    ): bool {
        if ($role === 'admin') {
            return true;
        }

        if (empty($expense->paid_by)) {
            return true;
        }

        return (int) $expense->paid_by === $currentUserId;
    }
}
