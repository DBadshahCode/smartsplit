<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class FinalDistribution extends Entity
{
    protected $data_map = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [
        'id'                   => 'integer',
        'user_id'              => 'integer',
        'month'                => 'string',
        'chapati_amount'       => 'float',
        'other_expenses_amount'=> 'float',
        'due_amount'          => 'float',
        'advance_amount'      => 'float',
        'final_amount'         => 'float',
        'generated_at'         => 'datetime',
    ];
    protected $attributes = [
        'id'                   => null,
        'user_id'              => null,
        'month'                => null,
        'chapati_amount'       => null,
        'other_expenses_amount'=> null,
        'due_amount'          => null,
        'advance_amount'      => null,
        'final_amount'         => null,
        'generated_at'         => null,
    ];
}
