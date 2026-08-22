<?php

namespace App\Controllers;

use App\Entities\Expense as ExpenseEntity;
use App\Models\AbsentDay as AbsentDayModel;
use App\Models\Expense as ExpenseModel;
use App\Models\ExpenseInvolvement as ExpenseInvolvementModel;
use App\Models\ExpenseType as ExpenseTypeModel;
use App\Models\User as UserModel;
use CodeIgniter\Database\BaseConnection;

class Expense extends BaseController
{
    protected ExpenseModel $expenseModel;
    protected ExpenseInvolvementModel $expenseInvolvementModel;
    protected ExpenseTypeModel $expenseTypeModel;
    protected UserModel $userModel;
    protected AbsentDayModel $absentDayModel;
    protected BaseConnection $db;

    public function __construct()
    {
        $this->expenseModel = new ExpenseModel();
        $this->expenseInvolvementModel = new ExpenseInvolvementModel();
        $this->expenseTypeModel = new ExpenseTypeModel();
        $this->userModel = new UserModel();
        $this->absentDayModel = new AbsentDayModel();

        $this->db = db_connect();
    }

    public function index()
    {
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
            'pageTitle' => 'Expenses',
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

        $involvedUserIds = $this->getInvolvedUserIds($data);

        if ($involvedUserIds === []) {
            return $this->badRequest(
                'At least one involved user is required.'
            );
        }

        $expenseData = $this->getExpenseData($data);

        $this->db->transStart();

        $expenseId = $this->expenseModel->insert($expenseData);

        if ($expenseId === false) {
            $this->db->transRollback();

            return $this->serverError(
                'Failed to create expense.'
            );
        }

        $this->insertInvolvements(
            (int) $expenseId,
            $involvedUserIds
        );

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->serverError(
                'Failed to create expense.'
            );
        }

        return $this->response->setJSON([
            'status' => 'success',
            'id' => (int) $expenseId,
        ]);
    }

    public function deleteExpense(int $id)
    {
        if (!$this->expenseExists($id)) {
            return $this->notFound('Expense not found.');
        }

        $this->db->transStart();

        $this->deleteRelatedRecords($id);

        $this->expenseModel->delete($id);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->serverError(
                'Failed to delete expense.'
            );
        }

        return $this->response->setJSON([
            'status' => 'deleted',
        ]);
    }

    public function bulkDeleteExpenses()
    {
        if (!$this->isAdmin()) {
            return $this->forbidden(
                'Admin access required.'
            );
        }

        $ids = $this->getExpenseIds();

        if ($ids === []) {
            return $this->badRequest(
                'No valid expenses selected.'
            );
        }

        $this->db->transStart();

        $this->absentDayModel
            ->whereIn('expense_id', $ids)
            ->delete();

        $this->expenseInvolvementModel
            ->whereIn('expense_id', $ids)
            ->delete();

        $this->expenseModel
            ->whereIn('id', $ids)
            ->delete();

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->serverError(
                'Failed to delete selected expenses.'
            );
        }

        return $this->response->setJSON([
            'status' => 'deleted',
            'deleted' => count($ids),
        ]);
    }

    public function getExpense(int $id)
    {
        $expense = $this->findExpense($id);

        if ($expense === null) {
            return $this->notFound('Expense not found.');
        }

        $involvedUserIds = $this->getInvolvedUserIdsByExpense(
            $id
        );

        $canEdit = $this->canEditExpense(
            $expense,
            $this->currentUser['role'],
            (int) $this->currentUser['id']
        );

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
                'involved_ids' => $involvedUserIds,
                'can_edit' => $canEdit,
            ],
        ]);
    }

    public function updateExpense(int $id)
    {
        $expense = $this->findExpense($id);

        if ($expense === null) {
            return $this->notFound('Expense not found.');
        }

        if (!$this->canEditExpense(
            $expense,
            (string) $this->currentUser['role'],
            (int) $this->currentUser['id']
        )) {
            return $this->forbidden(
                'You do not have permission to edit this expense.'
            );
        }

        $data = $this->request->getPost();

        $involvedUserIds = $this->getInvolvedUserIds($data);

        if ($involvedUserIds === []) {
            return $this->badRequest(
                'At least one involved user is required.'
            );
        }

        $expenseData = $this->getExpenseData($data);

        $this->db->transStart();

        $this->expenseModel->update(
            $id,
            $expenseData
        );

        $this->expenseInvolvementModel
            ->where('expense_id', $id)
            ->delete();

        $this->insertInvolvements(
            $id,
            $involvedUserIds
        );

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->serverError(
                'Failed to update expense.'
            );
        }

        return $this->response->setJSON([
            'status' => 'success',
        ]);
    }

    /**
     * Find an expense by ID.
     */
    private function findExpense(int $id): ?ExpenseEntity
    {
        $expense = $this->expenseModel->find($id);

        return $expense instanceof ExpenseEntity
            ? $expense
            : null;
    }

    /**
     * Check whether an expense exists.
     */
    private function expenseExists(int $id): bool
    {
        return $this->expenseModel->find($id) !== null;
    }

    /**
     * Prepare expense data from request.
     */
    private function getExpenseData(array $data): array
    {
        return [
            'expense_type_id' => $data['expense_type_id'] ?? null,
            'description' => $data['description'] ?? null,
            'amount' => $data['amount'] ?? null,
            'billing_month' => $data['billing_month'] ?? null,
            'from_date' => $data['from_date'] ?? null,
            'to_date' => $data['to_date'] ?? null,
            'paid_by' => !empty($data['paid_by'])
                ? $data['paid_by']
                : null,
        ];
    }

    /**
     * Get and normalize involved user IDs from request.
     *
     * @return int[]
     */
    private function getInvolvedUserIds(array $data): array
    {
        $userIds = $data['involved_users'] ?? [];

        if (!is_array($userIds)) {
            return [];
        }

        $userIds = array_map(
            static fn($userId): int => (int) $userId,
            $userIds
        );

        $userIds = array_filter(
            $userIds,
            static fn(int $userId): bool => $userId > 0
        );

        return array_values(
            array_unique($userIds)
        );
    }

    /**
     * Get involved user IDs for an expense.
     *
     * @return int[]
     */
    private function getInvolvedUserIdsByExpense(int $expenseId): array
    {
        $rows = $this->expenseInvolvementModel
            ->select('user_id')
            ->where('expense_id', $expenseId)
            ->findAll();

        return array_map(
            static fn($row): int => (int) $row->user_id,
            $rows
        );
    }

    /**
     * Insert expense involvement records.
     *
     * @param int[] $userIds
     */
    private function insertInvolvements(
        int $expenseId,
        array $userIds
    ): void {
        if ($userIds === []) {
            return;
        }

        $rows = array_map(
            static fn(int $userId): array => [
                'expense_id' => $expenseId,
                'user_id' => $userId,
            ],
            $userIds
        );

        $this->expenseInvolvementModel->insertBatch($rows);
    }

    /**
     * Delete records related to an expense.
     */
    private function deleteRelatedRecords(int $expenseId): void
    {
        $this->absentDayModel
            ->where('expense_id', $expenseId)
            ->delete();

        $this->expenseInvolvementModel
            ->where('expense_id', $expenseId)
            ->delete();
    }

    /**
     * Get valid expense IDs from request.
     *
     * @return int[]
     */
    private function getExpenseIds(): array
    {
        $ids = $this->request->getPost('ids');

        if (!is_array($ids)) {
            return [];
        }

        $ids = array_map(
            static fn($id): int => (int) $id,
            $ids
        );

        $ids = array_filter(
            $ids,
            static fn(int $id): bool => $id > 0
        );

        return array_values(
            array_unique($ids)
        );
    }

    /**
     * Determine whether the current user is an administrator.
     */
    private function isAdmin(): bool
    {
        return (string) $this->currentUser['role'] === 'admin';
    }

    /**
     * Determine whether the current user can edit an expense.
     *
     * Rules:
     * - Admins can always edit.
     * - Non-admins can edit expenses they paid for.
     * - Non-admins can edit expenses that have no payer assigned.
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

    /**
     * Return a 400 Bad Request response.
     */
    private function badRequest(string $message)
    {
        return $this->response
            ->setStatusCode(400)
            ->setJSON([
                'error' => $message,
            ]);
    }

    /**
     * Return a 403 Forbidden response.
     */
    private function forbidden(string $message)
    {
        return $this->response
            ->setStatusCode(403)
            ->setJSON([
                'error' => $message,
            ]);
    }

    /**
     * Return a 404 Not Found response.
     */
    private function notFound(string $message)
    {
        return $this->response
            ->setStatusCode(404)
            ->setJSON([
                'error' => $message,
            ]);
    }

    /**
     * Return a 500 Internal Server Error response.
     */
    private function serverError(string $message)
    {
        return $this->response
            ->setStatusCode(500)
            ->setJSON([
                'error' => $message,
            ]);
    }
}
