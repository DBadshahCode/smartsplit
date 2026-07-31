<?php

namespace App\Controllers;

use \Config\Database as DB;
use App\Controllers\BaseController;
use App\Entities\AbsentDay as AbsentDayEntity;
use App\Entities\Expense as ExpenseEntity;
use App\Entities\User as UserEntity;
use App\Models\AbsentDay as AbsentDayModel;
use App\Models\Expense as ExpenseModel;
use App\Models\User as UserModel;

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
        $page_title = 'Absent Days';
        return view('absentday/index', compact('page_title'));
    }

    /**
     * GET /absentdays/getExpenses
     * Returns only expenses with split_method = 'daysPresent' for the picker.
     */
    public function getExpenses()
    {
        $db = DB::connect();

        $expenses = $db->table('expenses e')
            ->select('e.id, e.from_date, e.to_date, e.amount, et.name AS expense_type')
            ->join('expense_types et', 'et.id = e.expense_type_id', 'left')
            ->where('et.split_method', 'daysPresent')
            ->orderBy('e.id', 'DESC')
            ->get()
            ->getResultArray();

        $data = [];
        foreach ($expenses as $exp) {
            $data[] = [
                'id' => (int) $exp['id'],
                'expense_type' => $exp['expense_type'] ?? '',
                'from_date' => substr((string) $exp['from_date'], 0, 10),
                'to_date' => substr((string) $exp['to_date'], 0, 10),
                'amount' => (float) $exp['amount'],
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    /**
     * GET /absentdays/getAbsentDays/:expense_id
     * Returns involved users with days_absent for this expense.
     * Users with no record get days_absent = 0.
     */
    public function getAbsentDays(int $expenseId)
    {
        $userModel = $this->userModel;
        $absentModel = $this->absentDayModel;
        $expenseModel = $this->expenseModel;

        $expense = $expenseModel->find($expenseId);
        if (!$expense) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Expense not found.']);
        }

        $fromDate = substr((string) ($expense instanceof ExpenseEntity ? $expense->from_date : $expense['from_date']), 0, 10);
        $toDate = substr((string) ($expense instanceof ExpenseEntity ? $expense->to_date : $expense['to_date']), 0, 10);
        $totalDays = (int) ((strtotime($toDate) - strtotime($fromDate)) / 86400) + 1;

        // Only users involved in this expense
        $db = DB::connect();
        $involvements = $db->table('expense_involvements')
            ->select('user_id')
            ->where('expense_id', $expenseId)
            ->get()
            ->getResultArray();

        $involvedIds = array_column($involvements, 'user_id');

        if (empty($involvedIds)) {
            return $this->response->setJSON(['data' => [], 'total_days' => $totalDays]);
        }

        $users = $userModel->whereIn('id', $involvedIds)->orderBy('name', 'ASC')->findAll();

        // Key absent records by user_id
        $absentRecords = $absentModel->where('expense_id', $expenseId)->findAll();
        $absentMap = [];
        foreach ($absentRecords as $record) {
            $uid = $record instanceof AbsentDayEntity ? $record->user_id : $record['user_id'];
            $days = $record instanceof AbsentDayEntity ? $record->days_absent : $record['days_absent'];
            $rid = $record instanceof AbsentDayEntity ? $record->id : $record['id'];
            $absentMap[(int) $uid] = ['id' => $rid, 'days_absent' => (int) $days];
        }

        $data = [];
        foreach ($users as $user) {
            $userId = $user instanceof UserEntity ? $user->id : $user['id'];
            $name = $user instanceof UserEntity ? $user->name : $user['name'];
            $entry = $absentMap[(int) $userId] ?? ['id' => null, 'days_absent' => 0];

            $data[] = [
                'record_id' => $entry['id'],
                'user_id' => (int) $userId,
                'name' => $name,
                'expense_id' => $expenseId,
                'days_absent' => $entry['days_absent'],
                'days_present' => max(0, $totalDays - $entry['days_absent']),
            ];
        }

        return $this->response->setJSON(['data' => $data, 'total_days' => $totalDays]);
    }

    /**
     * POST /absentdays/upsert
     * Body: expense_id, user_id, days_absent
     * Inserts or updates. Deletes record cleanly when days_absent = 0.
     */
    public function upsert()
    {
        $expenseId = (int) $this->request->getPost('expense_id');
        $userId = (int) $this->request->getPost('user_id');
        $daysAbsent = (int) $this->request->getPost('days_absent');

        if (!$expenseId || !$userId || $daysAbsent < 0) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid input.']);
        }

        // Validate upper bound against actual expense period length
        $expense = $this->expenseModel->find($expenseId);
        if (!$expense) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Expense not found.']);
        }

        $fromDate = substr((string) ($expense instanceof ExpenseEntity ? $expense->from_date : $expense['from_date']), 0, 10);
        $toDate = substr((string) ($expense instanceof ExpenseEntity ? $expense->to_date : $expense['to_date']), 0, 10);
        $totalDays = (int) ((strtotime($toDate) - strtotime($fromDate)) / 86400) + 1;

        if ($daysAbsent > $totalDays) {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => "Days absent cannot exceed {$totalDays} (total days in this expense period)."
            ]);
        }

        $absentModel = $this->absentDayModel;
        $existing = $absentModel
            ->where('expense_id', $expenseId)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            $recordId = $existing instanceof AbsentDayEntity ? $existing->id : $existing['id'];

            if ($daysAbsent === 0) {
                // Zero = fully present — delete to keep table clean
                $absentModel->delete($recordId);
                return $this->response->setJSON(['status' => 'deleted']);
            }

            $absentModel->update($recordId, ['days_absent' => $daysAbsent]);
            return $this->response->setJSON(['status' => 'updated', 'record_id' => $recordId]);
        }

        if ($daysAbsent === 0) {
            return $this->response->setJSON(['status' => 'noop']);
        }

        $newId = $absentModel->save([
            'expense_id' => $expenseId,
            'user_id' => $userId,
            'days_absent' => $daysAbsent,
        ]);

        return $this->response->setJSON(['status' => 'created', 'record_id' => $newId]);
    }

    /**
     * DELETE /absentdays/delete/:id  — admin only
     */
    public function delete(int $id)
    {
        $absentModel = $this->absentDayModel;
        $record = $absentModel->find($id);

        if (!$record) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Record not found.']);
        }

        $absentModel->delete($id);
        return $this->response->setJSON(['status' => 'deleted']);
    }
}
