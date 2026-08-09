<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            // Create atleast 10 users for testing purposes 1 admin and 9 regular users
            // name, email, role, password, created_at
            [
                'name' => 'Admin User',
                'email' => 'admin@demo.com',
                'role' => 'admin',
                'password' => password_hash('123', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Regular User 1',
                'email' => 'user1@demo.com',
                'role' => 'user',
                'password' => password_hash('123', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Regular User 2',
                'email' => 'user2@demo.com',
                'role' => 'user',
                'password' => password_hash('123', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Regular User 3',
                'email' => 'user3@demo.com',
                'role' => 'user',
                'password' => password_hash('123', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Regular User 4',
                'email' => 'user4@demo.com',
                'role' => 'user',
                'password' => password_hash('123', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Regular User 5',
                'email' => 'user5@demo.com',
                'role' => 'user',
                'password' => password_hash('123', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Regular User 6',
                'email' => 'user6@demo.com',
                'role' => 'user',
                'password' => password_hash('123', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Regular User 7',
                'email' => 'user7@demo.com',
                'role' => 'user',
                'password' => password_hash('123', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Regular User 8',
                'email' => 'user8@demo.com',
                'role' => 'user',
                'password' => password_hash('123', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Regular User 9',
                'email' => 'user9@demo.com',
                'role' => 'user',
                'password' => password_hash('123', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
            ]
        ];

        // Insert the users into your users table
        $this->db->table('users')->insertBatch($data);
    }
}
