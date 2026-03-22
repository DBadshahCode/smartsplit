<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ExpenseType as ExpenseTypeModel;
use App\Models\ChapatiExpense as ChapatiExpenseModel;
use App\Models\ChapatiAbsence as ChapatiAbsenceModel;
use App\Models\ChapatiExtraExpense as ChapatiExtraExpenseModel;
use App\Models\ChapatiExtraInvolvement as ChapatiExtraInvolvementModel;

class ChapatiExpense extends BaseController
{
    public function index()
    {
        $page_title = 'Chapati Expense Management';
        $expenseTypes = (new ExpenseTypeModel())->where('is_active', 1)->findAll();
        return view('chapatiexpense/index', compact('page_title', 'expenseTypes'));
    }
    public function getChapatiExpenses()
    {
        $model = new ChapatiExpenseModel();

        $expenses = $model
            ->select('
                                chapati_expenses.id,
                                chapati_expenses.total_amount,
                                chapati_expenses.from_date,
                                chapati_expenses.to_date,
                                expense_types.name as expense_type,')
            ->join('expense_types', 'expense_types.id = chapati_expenses.expense_type_id', 'left')
            ->orderBy('chapati_expenses.id', 'DESC')
            ->findAll();

        return $this->response->setJSON([
            'data' => $expenses
        ]);
    }
    public function addChapatiExpense()
    {
        $data = $this->request->getPost();
        (new ChapatiExpenseModel())->insert($data);
        return $this->response->setJSON(['status' => 'success']);
    }
    public function addAbsence()
    {
        $data = $this->request->getPost();
        (new ChapatiAbsenceModel())->insert($data);
        return $this->response->setJSON(['status' => 'success']);
    }
    public function addExtraExpense()
    {
        $data = $this->request->getPost();
        $extraId = (new ChapatiExtraExpenseModel())->insert([
            'chapati_expense_id' => $data['chapati_expense_id'],
            'item' => $data['item'],
            'amount' => $data['amount']
        ]);
        foreach ($data['shared_by'] as $uid) {
            (new ChapatiExtraInvolvementModel())->insert(
                ['extra_expense_id' => $extraId, 'user_id' => $uid]
            );
        }
        return $this->response->setJSON(['status' => 'success']);
    }
    public function deleteChapatiExpense($id)
{
    (new ChapatiExpenseModel())->delete($id);
    return $this->response->setJSON(['status' => 'deleted']);
}
}
