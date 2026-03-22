<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class ExpenseType extends Entity
{
    protected $data_map = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [
        'id'           => 'integer',
        'name'         => 'string',
        'description'  => 'string',
        'split_method' => 'string',
        'is_active'    => 'boolean',
    ];
    protected $attributes = [
        'id'           => null,
        'name'         => null,
        'description'  => null,
        'split_method' => null,
        'is_active'    => true,
    ];
}
