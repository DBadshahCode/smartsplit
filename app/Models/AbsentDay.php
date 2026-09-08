<?php

namespace App\Models;

use App\Entities\AbsentDay as AbsentDayEntity;
use CodeIgniter\Model;

class AbsentDay extends Model
{
    protected $table = 'absent_days';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = AbsentDayEntity::class;
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['user_id', 'expense_id', 'days_absent'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [];
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
     * Load absent days for many expenses in a single query.
     *
     * Callers that loop over expenses used to issue one query per expense that
     * needed absence data.
     *
     * @param  int[] $expenseIds
     * @return array<int, array<int, int>> [expense_id => [user_id => days_absent]]
     */
    public function getDaysByExpenseIds(array $expenseIds): array
    {
        if ($expenseIds === []) {
            return [];
        }

        $rows = $this->builder()
            ->select(['expense_id', 'user_id', 'days_absent'])
            ->whereIn('expense_id', $expenseIds)
            ->get()
            ->getResultArray();

        $map = [];

        foreach ($rows as $row) {
            $map[(int) $row['expense_id']][(int) $row['user_id']] = (int) $row['days_absent'];
        }

        return $map;
    }

    /**
     * Roster of every user involved in an expense together with their absence
     * record, in one query.
     *
     * Replaces the three-query sequence of "fetch involved user IDs", "fetch
     * those users" and "fetch absence rows". `expense_involvements` drives the
     * join so only involved users are returned; the LEFT JOIN keeps users who
     * have no absence row yet.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRosterForExpense(int $expenseId): array
    {
        return $this->db->table('expense_involvements ei')
            ->select([
                'ei.user_id',
                'u.name',
                'ad.id AS record_id',
                'COALESCE(ad.days_absent, 0) AS days_absent',
            ], false)
            ->join('users u', 'u.id = ei.user_id', 'inner')
            ->join(
                'absent_days ad',
                'ad.expense_id = ei.expense_id AND ad.user_id = ei.user_id',
                'left'
            )
            ->where('ei.expense_id', $expenseId)
            ->orderBy('u.name', 'ASC')
            ->get()
            ->getResultArray();
    }
}
