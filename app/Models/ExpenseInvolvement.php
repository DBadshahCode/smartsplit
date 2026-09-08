<?php

namespace App\Models;

use App\Entities\ExpenseInvolvement as ExpenseInvolvementEntity;
use CodeIgniter\Model;

class ExpenseInvolvement extends Model
{
    protected $table = 'expense_involvements';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = ExpenseInvolvementEntity::class;
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['expense_id', 'user_id'];

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
        'expense_id' => 'required|is_natural_no_zero',
        'user_id' => 'required|is_natural_no_zero',
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

    /**
     * Load the involved user IDs for many expenses in a single query.
     *
     * Callers that loop over expenses used to issue one query per expense.
     *
     * @param  int[] $expenseIds
     * @return array<int, int[]> [expense_id => [user_id, ...]]
     */
    public function getUserIdsByExpenseIds(array $expenseIds): array
    {
        if ($expenseIds === []) {
            return [];
        }

        $rows = $this->builder()
            ->select(['expense_id', 'user_id'])
            ->whereIn('expense_id', $expenseIds)
            ->get()
            ->getResultArray();

        $map = [];

        foreach ($rows as $row) {
            $map[(int) $row['expense_id']][] = (int) $row['user_id'];
        }

        return $map;
    }
}
