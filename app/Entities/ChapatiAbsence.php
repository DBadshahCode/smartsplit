<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class ChapatiAbsence extends Entity
{
    protected $data_map = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [
        'id'                  => 'integer',
        'chapati_expense_id'  => 'integer',
        'user_id'             => 'integer',
        'days_absent'         => 'integer',
    ];
    protected $attributes = [
        'id'                  => null,
        'chapati_expense_id'  => null,
        'user_id'             => null,
        'days_absent'         => null,
    ];
}
