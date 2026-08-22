<?php

namespace App\Models;

use App\Entities\Expense as ExpenseEntity;
use CodeIgniter\Model;

class Expense extends Model
{
    protected $table = 'expenses';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = ExpenseEntity::class;
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['expense_type_id', 'description', 'amount', 'billing_month', 'from_date', 'to_date', 'paid_by'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'from_date' => 'required|valid_date[Y-m-d]',
        'to_date' => 'required|valid_date[Y-m-d]',
        'billing_month' => 'permit_empty|regex_match[/^\d{4}-\d{2}$/]',
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    public function getExpensesForAbsentDays()
    {
        return $this->builder()
            ->select([
                'expenses.id',
                'expenses.from_date',
                'expenses.to_date',
                'expenses.amount',
                'expense_types.name AS expense_type',
            ])
            ->join(
                'expense_types',
                'expense_types.id = expenses.expense_type_id',
                'left'
            )
            ->where('expense_types.split_method', 'daysPresent')
            ->orderBy('expenses.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getInvolvedUserIds(int $expenseId)
    {
        $query = $this->db->table('expense_involvements')
            ->select('user_id')->distinct()
            ->where('expense_id', $expenseId)
            ->get()
            ->getResultArray();

        return array_map(fn($row) => (int) $row['user_id'], $query);
    }
}
