<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\FinalDistribution as FinalDistributionModel;
use App\Libraries\ExpenseCalculatorService;

class FinalDistribution extends BaseController
{
    public function index()
    {
        $page_title = 'Final Distribution';
        return view('finaldistribution/index', compact('page_title'));
    }
    public function getDistribution($month)
    {
        $finalDistributionModel = new FinalDistributionModel();
        $userModel = new \App\Models\User();

        $records = $finalDistributionModel->where('month', $month)->findAll();

        $data = [];
        foreach ($records as $record) {
            /** @var \App\Entities\User|null $user */
            $user = $userModel->find($record->user_id);
            $data[] = [
                'name' => $user ? $user->name : 'Unknown',
                'month' => $record->month,
                'chapati_amount' => $record->chapati_amount,
                'other_expenses_amount' => $record->other_expenses_amount,
                'advance_amount' => $record->advance_amount,
                'due_amount' => $record->due_amount,
                'final_amount' => $record->final_amount,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function generateDistribution($month)
    {
        $service = new ExpenseCalculatorService();
        $result = $service->calculateFinalDistribution($month);
        return $this->response->setJSON(['status' => 'success', 'data' => $result]);
    }
}
