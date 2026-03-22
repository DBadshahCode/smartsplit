<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
        ];

        // Insert the admin user into your users table
        $this->db->table('users')->insertBatch($data);
    }
}
