<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class ExpenseInvolvement extends Entity
{
    protected $data_map = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [
        'id'         => 'integer',
        'expense_id' => 'integer',
        'user_id'    => 'integer',
    ];
    protected $attributes = [
        'id'         => null,
        'expense_id' => null,
        'user_id'    => null,
    ];
}
