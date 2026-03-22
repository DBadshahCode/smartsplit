<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class ChapatiExpense extends Entity
{
    protected $data_map = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [
        'id'              => 'integer',
        'expense_type_id' => 'integer',
        'from_date'       => 'datetime',
        'to_date'         => 'datetime',
        'total_amount'    => 'float',
    ];
    protected $attributes = [
        'id'              => null,
        'expense_type_id' => null,
        'from_date'       => null,
        'to_date'         => null,
        'total_amount'    => null,
    ];
}
