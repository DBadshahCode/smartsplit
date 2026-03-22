<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ChapatiExtraExpense as ChapatiExtraExpenseModel;
use App\Models\ChapatiExpense as ChapatiExpenseModel;
use App\Models\ChapatiExtraInvolvement as ChapatiExtraInvolvementModel;
use App\Models\User as UserModel;

class ChapatiExtraExpense extends BaseController
{
    public function index()
    {
        $page_title = "Chapati Extra Expenses";

        $chapatiExpenses = (new ChapatiExpenseModel())
            ->orderBy('id', 'DESC')
            ->findAll();

        $session = session();
        $role = $session->get('role');
        $userId = $session->get('user_id');
        $userModel = new UserModel();
        $users = $userModel->findAll();

        return view('chapatiextraexpense/index', compact('page_title', 'chapatiExpenses', 'users', 'role', 'userId'));
    }


    public function getExtraExpenses()
    {
        $model = new ChapatiExtraExpenseModel();

        $data = $model
            ->select('
            chapati_extra_expenses.id,
            chapati_extra_expenses.item,
            chapati_extra_expenses.amount,
            chapati_expenses.from_date,
            chapati_expenses.to_date,
            COUNT(chapati_extra_involvements.user_id) as total_involved
        ')
            ->join('chapati_expenses', 'chapati_expenses.id = chapati_extra_expenses.chapati_expense_id')
            ->join('chapati_extra_involvements', 'chapati_extra_involvements.extra_expense_id = chapati_extra_expenses.id', 'left')
            ->groupBy('chapati_extra_expenses.id')
            ->findAll();

        return $this->response->setJSON([
            "data" => $data
        ]);
    }


    public function addExtraExpense()
    {
        $model = new ChapatiExtraExpenseModel();
        $chapatiExtraInvolvementModel = new ChapatiExtraInvolvementModel();

        $data = [
            "chapati_expense_id" => $this->request->getPost('chapati_expense_id'),
            "item" => $this->request->getPost('item'),
            "amount" => $this->request->getPost('amount')
        ];

        $chapatiExtraExpenseId = $model->insert($data);

        $users = $this->request->getPost('involved_users');

        if (!empty($users)) {
            foreach ($users as $uid) {
                $chapatiExtraInvolvementModel->insert([
                    'extra_expense_id' => $chapatiExtraExpenseId,
                    'user_id' => $uid
                ]);
            }
        }

        return $this->response->setJSON([
            "status" => true,
            "message" => "Extra expense added successfully"
        ]);
    }


    public function delete($id)
    {
        $model = new ChapatiExtraExpenseModel();

        $model->delete($id);

        return $this->response->setJSON([
            "status" => true,
            "message" => "Expense deleted"
        ]);
    }
}
