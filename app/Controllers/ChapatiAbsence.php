<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ChapatiAbsence as ChapatiAbsenceModel;
use App\Models\User as UserModel;
use App\Models\ChapatiExpense as ChapatiExpenseModel;

class ChapatiAbsence extends BaseController
{
    public function index()
    {
        $page_title = 'Chapati Absence Management';
        $users = (new UserModel())->findAll();
        $chapatiExpenses = (new ChapatiExpenseModel())->findAll();
        return view('chapatiabsence/index', compact('page_title', 'users', 'chapatiExpenses'));
    }
    public function getAbsences()
    {
        $model = new ChapatiAbsenceModel();
        $absences = $model->select('chapati_absences.id, users.name as user_name, chapati_expenses.id as chapati_expense_id, chapati_absences.days_absent')->join('users', 'users.id = chapati_absences.user_id', 'left')->join('chapati_expenses', 'chapati_expenses.id = chapati_absences.chapati_expense_id', 'left')->orderBy('chapati_absences.id', 'DESC')->findAll();
        return $this->response->setJSON(['data' => $absences]);
    }
    public function addAbsence()
    {
        $data = $this->request->getPost();
        (new ChapatiAbsenceModel())->insert($data);
        return $this->response->setJSON(['status' => 'success']);
    }
    public function deleteAbsence($id)
    {
        (new ChapatiAbsenceModel())->delete($id);
        return $this->response->setJSON(['status' => 'deleted']);
    }
}
