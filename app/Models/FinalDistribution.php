<?php

namespace App\Models;

use App\Entities\FinalDistribution as FinalDistributionEntity;
use CodeIgniter\Model;

class FinalDistribution extends Model
{
    protected $table = 'final_distributions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = FinalDistributionEntity::class;
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['month', 'user_id', 'expenses_amount', 'due_amount', 'advance_amount', 'final_amount', 'generated_at'];

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
     * Month whose distribution was generated most recently.
     *
     * Ordered by generated_at rather than by the month string so that
     * re-generating an older month surfaces it as the latest.
     */
    public function getLatestGeneratedMonth(): ?string
    {
        $row = $this->builder()
            ->select('month')
            ->orderBy('generated_at', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        return $row['month'] ?? null;
    }

    /**
     * Every distribution row for a month, with the user's name resolved.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getMonthWithUserNames(string $month): array
    {
        return $this->builder()
            ->select([
                'final_distributions.month',
                'final_distributions.expenses_amount',
                'final_distributions.advance_amount',
                'final_distributions.due_amount',
                'final_distributions.final_amount',
                'final_distributions.generated_at',
                'users.name AS user_name',
            ])
            ->join(
                'users',
                'users.id = final_distributions.user_id',
                'left'
            )
            ->where('final_distributions.month', $month)
            ->orderBy('users.name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Per-user amounts for a month, keyed by user ID.
     *
     * @return array<int, array{
     *     expenses_amount: float,
     *     advance_amount: float,
     *     final_amount: float
     * }>
     */
    public function getAmountsMapForMonth(string $month): array
    {
        $rows = $this->builder()
            ->select([
                'user_id',
                'expenses_amount',
                'advance_amount',
                'final_amount',
            ])
            ->where('month', $month)
            ->get()
            ->getResultArray();

        $map = [];

        foreach ($rows as $row) {
            $map[(int) $row['user_id']] = [
                'expenses_amount' => (float) $row['expenses_amount'],
                'advance_amount' => (float) $row['advance_amount'],
                'final_amount' => (float) $row['final_amount'],
            ];
        }

        return $map;
    }

    /**
     * Latest month that has a distribution row for one user.
     */
    public function getLatestMonthForUser(int $userId): ?string
    {
        $row = $this->builder()
            ->select('month')
            ->where('user_id', $userId)
            ->orderBy('month', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        return $row['month'] ?? null;
    }

    /**
     * One user's distribution row for a month.
     *
     * @return array<string, mixed>|null
     */
    public function getRowForUserMonth(int $userId, string $month): ?array
    {
        return $this->builder()
            ->select([
                'month',
                'expenses_amount',
                'advance_amount',
                'due_amount',
                'final_amount',
                'generated_at',
            ])
            ->where('user_id', $userId)
            ->where('month', $month)
            ->limit(1)
            ->get()
            ->getRowArray();
    }
}
