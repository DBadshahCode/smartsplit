<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class User extends Entity
{
    protected $data_map = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [
        'id'          => 'integer',
        'name'        => 'string',
        'email'       => 'string',
        'role'        => 'string',
        'joined_date' => 'datetime',
    ];
    protected $attributes = [
        'id' => null,
        'name' => null,
        'email' => null,
        'password' => null,
        'role' => null,
        'joined_at' => null,
    ];
}
