<?php

namespace App\Models;

use CodeIgniter\Model;

class ChapatiExtraExpense extends Model
{
    protected $table = 'chapati_extra_expenses';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = \App\Entities\ChapatiExtraExpense::class;
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['chapati_expense_id', 'item', 'amount'];

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

        'expense_id'
        => 'required|is_natural_no_zero',

        'user_id'
        => 'permit_empty|is_natural_no_zero',

        'description'
        => 'permit_empty',

        'amount'
        => 'required|decimal',

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
