<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class ChapatiExtraExpense extends Entity
{
    protected $data_map = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [
        'id'                => 'integer',
        'chapati_expense_id' => 'integer',
        'item'              => 'string',
        'amount'            => 'float',
    ];
    protected $attributes = [
        'id'                 => null,
        'chapati_expense_id' => null,
        'item'               => null,
        'amount'             => null,
    ];
}
