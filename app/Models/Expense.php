<?php

namespace App\Models;

use CodeIgniter\Model;

class Expense extends Model
{
    protected $table = 'expenses';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = \App\Entities\Expense::class;
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
}
