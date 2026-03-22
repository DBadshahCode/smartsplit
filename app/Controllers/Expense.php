<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ExpenseType as ExpenseTypeModel;
use App\Models\Expense as ExpenseModel;
use App\Models\ExpenseInvolvement as ExpenseInvolvementModel;
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
        $userModel = new UserModel();
        $users = $userModel->findAll();

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

        $builder = $expenseModel
            ->select('
                                expenses.id,
                                expenses.amount,
                                expenses.from_date,
                                expenses.to_date,
                                expense_types.name as expense_type,
                                users.name as paid_by_name,
                                COUNT(expense_involvements.user_id) as total_involved
                            ')
            ->join('expense_types', 'expense_types.id = expenses.expense_type_id', 'left')
            ->join('users', 'users.id = expenses.paid_by', 'left')
            ->join('expense_involvements', 'expense_involvements.expense_id = expenses.id', 'left')
            ->groupBy('expenses.id');


        $expenses = $builder->findAll();

        return $this->response->setJSON([
            'data' => $expenses
        ]);
    }
    public function addExpense()
    {
        $expenseModel = new ExpenseModel();
        $involvementModel = new ExpenseInvolvementModel();
        $data = $this->request->getPost();
        $paidBy = $this->request->getPost('paid_by');
        $paidBy = $paidBy ? $paidBy : null;
        $expenseId = $expenseModel->insert([
            'expense_type_id' => $data['expense_type_id'],
            'amount' => $data['amount'],
            'from_date' => $data['from_date'],
            'to_date' => $data['to_date'],
            'paid_by' => $paidBy,
        ]);
        foreach ($data['involved_users'] as $uid) {
            $involvementModel->insert(
                ['expense_id' => $expenseId, 'user_id' => $uid]
            );
        }
        return $this->response->setJSON(['status' => 'success']);
    }
    public function deleteExpense($id)
    {
        (new ExpenseModel())->delete($id);
        return $this->response->setJSON(['status' => 'deleted']);
    }
}
