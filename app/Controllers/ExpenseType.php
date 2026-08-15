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
        $pageTitle = 'Expense Types';
        return view('expensetype/index', $this->viewData([
            'page_title' => $pageTitle
        ]));
    }

    public function getExpenseTypes()
    {
        $data = $this->expenseTypeModel->findAll();

        return $this->response->setJSON([
            'data' => $data
        ]);
    }

    public function addExpenseType()
    {
        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'split_method' => $this->request->getPost('split_method'),
            'is_active' => $this->request->getPost('is_active')
        ];

        if ($this->expenseTypeModel->insert($data)) {
            return $this->response->setJSON([
                'status' => 'success'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error'
        ]);
    }

    public function deleteExpenseType(int $id)
    {
        if ($this->expenseTypeModel->delete($id)) {
            return $this->response->setJSON([
                'status' => 'success'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error'
        ]);
    }
}
