<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Expense as ExpenseModel;
use App\Models\ExpenseInvolvement as ExpenseInvolvementModel;
use App\Models\ExpenseType as ExpenseTypeModel;
use App\Models\User as UserModel;

class Expense extends BaseController
{
    public function index()
    {
        $page_title = 'Expense Management';

        $expenseTypes = (new ExpenseTypeModel())
            ->where('is_active', 1)
            ->findAll();

        $session = session();
        $role = $session->get('role');
        $userId = $session->get('user_id');
        $users = (new UserModel())->findAll();

        return view('expense/index', compact(
            'page_title',
            'expenseTypes',
            'users',
            'role',
            'userId'
        ));
    }

    public function getExpenses()
    {
        $expenseModel = new ExpenseModel();

        $expenses = $expenseModel
            ->select('
                expenses.id,
                expenses.description,
                expenses.amount,
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
        $expenseModel = new ExpenseModel();
        $involvementModel = new ExpenseInvolvementModel();
        $data = $this->request->getPost();
        $paidBy = $this->request->getPost('paid_by') ?: null;

        $expenseId = $expenseModel->insert([
            'expense_type_id' => $data['expense_type_id'],
            'description' => $data['description'],
            'amount' => $data['amount'],
            'from_date' => $data['from_date'],
            'to_date' => $data['to_date'],
            'paid_by' => $paidBy,
        ]);

        foreach ($data['involved_users'] as $uid) {
            $involvementModel->insert([
                'expense_id' => $expenseId,
                'user_id' => $uid,
            ]);
        }

        return $this->response->setJSON(['status' => 'success']);
    }

    public function deleteExpense($id)
    {
        (new ExpenseModel())->delete($id);
        return $this->response->setJSON(['status' => 'deleted']);
    }

    public function getExpense($id)
    {
        $expenseModel = new ExpenseModel();
        $involvementModel = new ExpenseInvolvementModel();

        /** @var \App\Entities\Expense|null $expense */
        $expense = $expenseModel->find($id);
        if (!($expense instanceof \App\Entities\Expense)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        }

        // Collect involved user IDs for this expense
        $rows = $involvementModel->where('expense_id', $id)->findAll();
        $involvedIds = array_map(fn($r) => (int) $r->user_id, $rows);

        return $this->response->setJSON([
            'data' => [
                'id' => (int) $expense->id,
                'expense_type_id' => (int) $expense->expense_type_id,
                'description' => $expense->description,
                'amount' => $expense->amount,
                'from_date' => (string) $expense->from_date,
                'to_date' => (string) $expense->to_date,
                'paid_by' => $expense->paid_by,
                'involved_ids' => $involvedIds,
            ],
        ]);
    }

    public function updateExpense($id)
    {
        $expenseModel = new ExpenseModel();
        $involvementModel = new ExpenseInvolvementModel();

        /** @var \App\Entities\Expense|null $expense */
        $expense = $expenseModel->find($id);
        if (!($expense instanceof \App\Entities\Expense)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        }

        $data = $this->request->getPost();
        $paidBy = $this->request->getPost('paid_by') ?: null;

        $expenseModel->update($id, [
            'expense_type_id' => $data['expense_type_id'],
            'description' => $data['description'],
            'amount' => $data['amount'],
            'from_date' => $data['from_date'],
            'to_date' => $data['to_date'],
            'paid_by' => $paidBy,
        ]);

        // Replace involvements: delete old, insert new
        $involvementModel->where('expense_id', $id)->delete();
        foreach ($data['involved_users'] as $uid) {
            $involvementModel->insert([
                'expense_id' => $id,
                'user_id' => $uid,
            ]);
        }

        return $this->response->setJSON(['status' => 'success']);
    }
}