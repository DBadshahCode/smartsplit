<?php

namespace App\Models;

use App\Entities\User as UserEntity;
use CodeIgniter\Model;

class User extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = UserEntity::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'email', 'password', 'role', 'joined_date', 'recovery_code_hash'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Users for the admin management table.
     *
     * Explicitly column-listed so the password hash never leaves the database.
     * `findAll()` selects every column, which sent each user's hash to the
     * browser in the JSON payload.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getUserList(): array
    {
        return $this->builder()
            ->select(['id', 'name', 'email', 'role', 'joined_date'])
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * id/name pairs for pickers and dropdowns.
     *
     * Returned as plain rows: the callers only read these two columns, so
     * hydrating a full entity per user is wasted work.
     *
     * @return array<int, array{id: int|string, name: string}>
     */
    public function getIdNameList(): array
    {
        return $this->builder()
            ->select(['id', 'name'])
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Whether an email address is already used by a different user.
     */
    public function isEmailTakenByOther(string $email, int $exceptUserId): bool
    {
        return $this->builder()
            ->select('1', false)
            ->where('email', $email)
            ->where('id !=', $exceptUserId)
            ->limit(1)
            ->get()
            ->getRowArray() !== null;
    }

    /**
     * Number of users holding the admin role.
     */
    public function countAdmins(): int
    {
        return $this->builder()
            ->where('role', 'admin')
            ->countAllResults();
    }

    /**
     * Find a user by email for auth flows (login, forgot-password).
     * Returns the full entity — including password/recovery_code_hash —
     * since this is only ever called server-side, never exposed via JSON.
     */
    public function findByEmail(string $email): ?UserEntity
    {
        /** @var UserEntity|null $user */
        $user = $this->where('email', $email)->first();

        return $user instanceof UserEntity ? $user : null;
    }
}
