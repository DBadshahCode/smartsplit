<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AbsentDay as AbsentDayModel;
use App\Models\Expense as ExpenseModel;
use App\Models\User as UserModel;
use DateTimeImmutable;

class AbsentDay extends BaseController
{
    protected AbsentDayModel $absentDayModel;
    protected UserModel $userModel;
    protected ExpenseModel $expenseModel;

    public function __construct()
    {
        $this->absentDayModel = new AbsentDayModel();
        $this->userModel = new UserModel();
        $this->expenseModel = new ExpenseModel();
    }

    public function index()
    {
        return view('absentday/index', $this->viewData([
            'pageTitle' => 'Absent Days',
        ]));
    }

    /**
     * GET /absentdays/getExpenses
     */
    public function getExpenses()
    {
        $expenses = $this->expenseModel->getExpensesForAbsentDays();

        return $this->response->setJSON([
            'data' => $expenses,
        ]);
    }

    /**
     * GET /absentdays/getAbsentDays/:expenseId
     */
    public function getAbsentDays(int $expenseId)
    {
        $expense = $this->expenseModel->find($expenseId);

        if ($expense === null) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'error' => 'Expense not found.',
                ]);
        }

        $fromDate = $this->normalizeDate($expense->from_date);
        $toDate = $this->normalizeDate($expense->to_date);

        $totalDays = $this->calculateDaysInclusive($fromDate, $toDate);

        $userIds = $this->expenseModel->getInvolvedUserIds($expenseId);

        if ($userIds === []) {
            return $this->response->setJSON([
                'data' => [],
                'total_days' => $totalDays,
            ]);
        }

        $users = $this->userModel
            ->whereIn('id', $userIds)
            ->orderBy('name', 'ASC')
            ->findAll();

        $absentRecords = $this->absentDayModel
            ->where('expense_id', $expenseId)
            ->findAll();

        $absentMap = [];

        foreach ($absentRecords as $record) {
            $absentMap[(int) $record->user_id] = [
                'id' => (int) $record->id,
                'days_absent' => (int) $record->days_absent,
            ];
        }

        $data = [];

        foreach ($users as $user) {
            $userId = (int) $user->id;

            $daysAbsent = $absentMap[$userId]['days_absent'] ?? 0;
            $recordId = $absentMap[$userId]['id'] ?? null;

            $data[] = [
                'record_id' => $recordId,
                'user_id' => $userId,
                'name' => $user->name,
                'expense_id' => $expenseId,
                'days_absent' => $daysAbsent,
                'days_present' => max(0, $totalDays - $daysAbsent),
            ];
        }

        return $this->response->setJSON([
            'data' => $data,
            'total_days' => $totalDays,
        ]);
    }

    /**
     * POST /absentdays/upsert
     */
    public function upsert()
    {
        $expenseId = (int) $this->request->getPost('expense_id');
        $userId = (int) $this->request->getPost('user_id');
        $daysAbsent = (int) $this->request->getPost('days_absent');

        if ($expenseId <= 0 || $userId <= 0 || $daysAbsent < 0) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'error' => 'Invalid input.',
                ]);
        }

        $expense = $this->expenseModel->find($expenseId);

        if ($expense === null) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'error' => 'Expense not found.',
                ]);
        }

        /**
         * Important:
         * Do not allow absent-day records for users who are not
         * actually involved in this expense.
         */
        if (!$this->expenseModel->hasInvolvedUser($expenseId, $userId)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'error' => 'User is not involved in this expense.',
                ]);
        }

        $fromDate = $this->normalizeDate($expense->from_date);
        $toDate = $this->normalizeDate($expense->to_date);

        $totalDays = $this->calculateDaysInclusive($fromDate, $toDate);

        if ($daysAbsent > $totalDays) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'error' => sprintf(
                        'Days absent cannot exceed %d (total days in this expense period).',
                        $totalDays
                    ),
                ]);
        }

        $existing = $this->absentDayModel
            ->where('expense_id', $expenseId)
            ->where('user_id', $userId)
            ->first();

        if ($existing !== null) {
            if ($daysAbsent === 0) {
                $this->absentDayModel->delete($existing->id);

                return $this->response->setJSON([
                    'status' => 'deleted',
                ]);
            }

            $this->absentDayModel->update($existing->id, [
                'days_absent' => $daysAbsent,
            ]);

            return $this->response->setJSON([
                'status' => 'updated',
                'record_id' => $existing->id,
            ]);
        }

        if ($daysAbsent === 0) {
            return $this->response->setJSON([
                'status' => 'noop',
            ]);
        }

        $recordId = $this->absentDayModel->insert([
            'expense_id' => $expenseId,
            'user_id' => $userId,
            'days_absent' => $daysAbsent,
        ], true);

        return $this->response->setJSON([
            'status' => 'created',
            'record_id' => $recordId,
        ]);
    }

    /**
     * DELETE /absentdays/delete/:id
     */
    public function delete(int $id)
    {
        $record = $this->absentDayModel->find($id);

        if ($record === null) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'error' => 'Record not found.',
                ]);
        }

        $this->absentDayModel->delete($id);

        return $this->response->setJSON([
            'status' => 'deleted',
        ]);
    }

    private function normalizeDate(string $date)
    {
        return (new DateTimeImmutable($date))->format('Y-m-d');
    }

    private function calculateDaysInclusive(string $fromDate, string $toDate) : int
    {
        $from = new DateTimeImmutable($fromDate);
        $to = new DateTimeImmutable($toDate);

        if ($to < $from) {
            return 0;
        }

        return $from->diff($to)->days + 1;
    }
}
