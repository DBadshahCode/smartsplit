<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Expense extends Entity
{
    protected $data_map = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [
        'id'              => 'integer',
        'expense_type_id' => 'integer',
        'amount'          => 'float',
        'from_date'       => 'datetime',
        'to_date'         => 'datetime',
        'paid_by'         => 'integer',
    ];
    protected $attributes = [
        'id'              => null,
        'expense_type_id' => null,
        'amount'          => null,
        'from_date'       => null,
        'to_date'         => null,
        'paid_by'         => null,
    ];
}
