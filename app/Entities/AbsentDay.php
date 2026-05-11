<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class AbsentDay extends Entity
{
    protected $datamap = [];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'expense_id' => 'integer',
        'days_absent' => 'integer',
    ];
    protected $attributes = [
        'id' => null,
        'user_id' => null,
        'expense_id' => null,
        'days_absent' => null,
    ];
}
