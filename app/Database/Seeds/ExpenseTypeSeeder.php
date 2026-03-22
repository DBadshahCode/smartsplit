<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ExpenseTypeSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name' => 'Rent',
                'description' => 'Monthly rent for the apartment',
                'split_method' => 'equal',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Electricity Bill',
                'description' => 'Electricity bill for the month',
                'split_method' => 'equal',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Wifi Bill',
                'description' => 'Wifi bill for the month',
                'split_method' => 'equal',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Groceries',
                'description' => 'Shared groceries expenses',
                'split_method' => 'equal',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Gas Bill',
                'description' => 'Gas bill for the month',
                'split_method' => 'equal',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Chapati',
                'description' => 'Chapati expenses for the month',
                'split_method' => 'daysPresent',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Maid',
                'description' => 'Maid expenses for the month',
                'split_method' => 'equal',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Repairs',
                'description' => 'Repairs expenses for the apartment',
                'split_method' => 'equal',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Insert the admin user into your users table
        $this->db->table('expense_types')->insertBatch($data);
    }
}
