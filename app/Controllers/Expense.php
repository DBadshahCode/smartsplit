<?php

namespace App\Controllers;

use App\Controllers\BaseController;
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
            ->where('is_active', 1)
            ->findAll();

        $session = session();
        $currentUser = [
            'id' => (int) $session->get('user_id'),
            'name' => $session->get('name'),
            'role' => $session->get('role'),
        ];
        $users = $this->userModel->findAll();

        return view('expense/index', compact(
            'page_title',
            'expenseTypes',
            'users',
            'currentUser'
        ));
    }

    public function getExpenses()
    {
        $expenses = $this->expenseModel
            ->select('
                expenses.id,
                expenses.description,
                expenses.amount,
                expenses.billing_month,
                expenses.from_date,
                expenses.to_date,
                expense_types.name as expense_type,
                users.name as paid_by_name,
                COUNT(expense_involvements.user_id) as total_involved,
                GROUP_CONCAT(involved_users.name ORDER BY involved_users.name SEPARATOR \', \') as involved_names
            ')
            ->join('expense_types', 'expense_types.id = expenses.expense_type_id', 'left')
            ->join('users', 'users.id = expenses.paid_by', 'left')
            ->join('expense_involvements', 'expense_involvements.expense_id = expenses.id', 'left')
            ->join('users as involved_users', 'involved_users.id = expense_involvements.user_id', 'left')
            ->groupBy('expenses.id')
            ->orderBy('expenses.id', 'DESC')
            ->findAll();

        return $this->response->setJSON([
            'data' => $expenses
        ]);
    }

    public function addExpense()
    {
        $data = $this->request->getPost();
        $paidBy = $this->request->getPost('paid_by') ?: null;

        $expenseId = $this->expenseModel->insert([
            'expense_type_id' => $data['expense_type_id'],
            'description' => $data['description'],
            'amount' => $data['amount'],
            'billing_month' => $data['billing_month'],
            'from_date' => $data['from_date'],
            'to_date' => $data['to_date'],
            'paid_by' => $paidBy,
        ]);

        foreach ($data['involved_users'] as $uid) {
            $this->expenseInvolvementModel->insert([
                'expense_id' => $expenseId,
                'user_id' => $uid,
            ]);
        }

        return $this->response->setJSON(['status' => 'success']);
    }

    public function deleteExpense($id)
    {
        //if absentdays exist, delete them first
        $this->absentDayModel->where('expense_id', $id)->delete();
        $this->expenseInvolvementModel->where('expense_id', $id)->delete();
        $this->expenseModel->delete($id);
        return $this->response->setJSON(['status' => 'deleted']);
    }

    public function bulkDeleteExpenses()
    {
        if ((string) session()->get('role') !== 'admin') {
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

        $db = \Config\Database::connect();
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

    public function getExpense($id)
    {
        /** @var \App\Entities\Expense|null $expense */
        $expense = $this->expenseModel->find($id);
        if (!($expense instanceof \App\Entities\Expense)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        }

        // Collect involved user IDs for this expense
        $rows = $this->expenseInvolvementModel->where('expense_id', $id)->findAll();
        $involvedIds = array_map(fn($r) => (int) $r->user_id, $rows);

        // Determine if current user can edit this expense
        $currentUser = [
            'id' => (int) session()->get('user_id'),
            'name' => session()->get('name'),
            'role' => session()->get('role'),
        ];
        $canEdit = $this->canEditExpense($expense, $currentUser['role'], $currentUser['id']);

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

    public function updateExpense($id)
    {
        /** @var \App\Entities\Expense|null $expense */
        $expense = $this->expenseModel->find($id);
        if (!($expense instanceof \App\Entities\Expense)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        }

        // Check if user has permission to edit this expense
        $currentUser = [
            'id' => (int) session()->get('user_id'),
            'name' => session()->get('name'),
            'role' => session()->get('role'),
        ];
        if (!$this->canEditExpense($expense, $currentUser['role'], $currentUser['id'])) {
            return $this->response->setStatusCode(403)->setJSON([
                'error' => 'You do not have permission to edit this expense'
            ]);
        }

        $data = $this->request->getPost();
        $paidBy = $this->request->getPost('paid_by') ?: null;

        $this->expenseModel->update($id, [
            'expense_type_id' => $data['expense_type_id'],
            'description' => $data['description'],
            'amount' => $data['amount'],
            'billing_month' => $data['billing_month'],
            'from_date' => $data['from_date'],
            'to_date' => $data['to_date'],
            'paid_by' => $paidBy,
        ]);

        // Replace involvements: delete old, insert new
        $this->expenseInvolvementModel->where('expense_id', $id)->delete();
        foreach ($data['involved_users'] as $uid) {
            $this->expenseInvolvementModel->insert([
                'expense_id' => $id,
                'user_id' => $uid,
            ]);
        }

        return $this->response->setJSON(['status' => 'success']);
    }

    /**
     * Determine if the current user can edit this expense
     * Rules:
     * - Admins can always edit
     * - Non-admins can only edit if they are the paid_by user
     * - Non-admins can edit if paid_by is null/empty (not assigned)
     */
    private function canEditExpense($expense, $role, $currentUserId)
    {
        // Admin can always edit
        if ($role === 'admin') {
            return true;
        }

        // Non-admin can edit if paid_by is not set or if they are the paid_by user
        if ($expense->paid_by === null || (int) $expense->paid_by === 0 || $expense->paid_by === '') {
            return true;
        }

        if ((int) $expense->paid_by === $currentUserId) {
            return true;
        }

        return false;
    }
}