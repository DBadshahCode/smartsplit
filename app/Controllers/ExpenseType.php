<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ExpenseType as ExpenseTypeModel;

class ExpenseType extends BaseController
{
    protected ExpenseTypeModel $expenseTypeModel;

    public function __construct()
    {
        $this->expenseTypeModel = new ExpenseTypeModel();
    }

    // Load main page
    public function index()
    {
        return view('expensetype/index', $this->viewData([
            'pageTitle' => 'Expense Types'
        ]));
    }

    public function getExpenseTypes()
    {
        return $this->response->setJSON([
            'data' => $this->expenseTypeModel->findAll(),
        ]);
    }

    public function addExpenseType()
    {
        $data = $this->request->getPost([
            'name',
            'description',
            'split_method',
            'is_active',
        ]);

        if (! $this->expenseTypeModel->insert($data)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status' => 'error',
                    'errors' => $this->expenseTypeModel->errors(),
                ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Expense type created successfully.',
            'id' => $this->expenseTypeModel->getInsertID(),
        ]);
    }

    public function deleteExpenseType(int $id)
    {
        if (! $this->expenseTypeModel->delete($id)) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Expense type not found.',
                ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Expense type deleted successfully.',
        ]);
    }
}
