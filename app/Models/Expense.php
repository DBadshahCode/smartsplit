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

    /**
     * List every expense with its type, payer and involvement summary.
     *
     * Involvements are aggregated in a derived table instead of being joined
     * into the outer query. Joining them directly multiplies the result set by
     * the number of involved users per expense, which the outer GROUP BY then
     * has to collapse again. Aggregating first keeps the outer query at one row
     * per expense.
     *
     * The DISTINCT inside both aggregates is deliberate and matches the
     * previous behaviour: total_involved counts users, while involved_names
     * de-duplicates by name, so two involved users sharing a name are counted
     * twice but listed once.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getExpenseList(): array
    {
        $involvements = $this->db->table('expense_involvements ei')
            ->select([
                'ei.expense_id',
                'COUNT(DISTINCT ei.user_id) AS total_involved',
                'GROUP_CONCAT(DISTINCT iu.name ORDER BY iu.name SEPARATOR ", ") AS involved_names',
            ], false)
            ->join('users iu', 'iu.id = ei.user_id', 'left')
            ->groupBy('ei.expense_id')
            ->getCompiledSelect();

        return $this->builder()
            ->select([
                'expenses.id',
                'expenses.description',
                'expenses.amount',
                'expenses.billing_month',
                'expenses.from_date',
                'expenses.to_date',
                'expense_types.name AS expense_type',
                'users.name AS paid_by_name',
                'COALESCE(inv.total_involved, 0) AS total_involved',
                'inv.involved_names',
            ], false)
            ->join(
                'expense_types',
                'expense_types.id = expenses.expense_type_id',
                'left'
            )
            ->join(
                'users',
                'users.id = expenses.paid_by',
                'left'
            )
            ->join(
                '(' . $involvements . ') inv',
                'inv.expense_id = expenses.id',
                'left',
                false
            )
            ->orderBy('expenses.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Fetch every expense in a billing month together with its split method.
     *
     * Returning the split method here removes the per-expense expense_types
     * lookup that callers used to perform inside their loops.
     *
     * `type_id` is null only when the joined expense type row is missing, which
     * lets callers distinguish "unknown type" from "type exists but has no
     * split_method".
     *
     * @return array<int, array<string, mixed>>
     */
    public function getExpensesForMonth(string $month): array
    {
        return $this->builder()
            ->select([
                'expenses.id',
                'expenses.amount',
                'expenses.from_date',
                'expenses.to_date',
                'expenses.billing_month',
                'expenses.paid_by',
                'expense_types.id AS type_id',
                'expense_types.name AS expense_type',
                'expense_types.split_method',
                'users.name AS paid_by_name',
            ])
            ->join(
                'expense_types',
                'expense_types.id = expenses.expense_type_id',
                'left'
            )
            ->join(
                'users',
                'users.id = expenses.paid_by',
                'left'
            )
            ->where('expenses.billing_month', $month)
            ->orderBy('expenses.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Check whether an expense exists without hydrating the whole entity.
     */
    public function existsById(int $id): bool
    {
        return $this->builder()
            ->select('1', false)
            ->where('id', $id)
            ->limit(1)
            ->get()
            ->getRowArray() !== null;
    }

    /**
     * @return int[]
     */
    public function getInvolvedUserIds(int $expenseId)
    {
        // No DISTINCT needed: expense_involvements has a UNIQUE key on
        // (expense_id, user_id), so a user cannot appear twice per expense.
        $query = $this->db->table('expense_involvements')
            ->select('user_id')
            ->where('expense_id', $expenseId)
            ->get()
            ->getResultArray();

        return array_map(static fn($row): int => (int) $row['user_id'], $query);
    }

    public function hasInvolvedUser(int $expenseId, int $userId): bool
    {
        return $this->db->table('expense_involvements')
            ->where('expense_id', $expenseId)
            ->where('user_id', $userId)
            ->countAllResults() > 0;
    }
}
