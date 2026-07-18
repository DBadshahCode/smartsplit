<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use RuntimeException;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        // Get available user IDs
        $userIds = array_column(
            $this->db->table('users')
                ->select('id')
                ->get()
                ->getResultArray(),
            'id'
        );

        if (empty($userIds)) {
            throw new RuntimeException('No users found. Please run UserSeeder first.');
        }

        // Get available expense type IDs
        $expenseTypeIds = array_column(
            $this->db->table('expense_types')
                ->select('id')
                ->get()
                ->getResultArray(),
            'id'
        );

        if (empty($expenseTypeIds)) {
            throw new RuntimeException('No expense types found. Please run ExpenseTypeSeeder first.');
        }

        $userCount = count($userIds);
        $expenseTypeCount = count($expenseTypeIds);

        $expenseTable = $this->db->table('expenses');
        $expenseInvolvements = [];

        $this->db->transStart();

        for ($i = 1; $i <= 20; $i++) {
            $expenseTable->insert([
                'expense_type_id' => $expenseTypeIds[array_rand($expenseTypeIds)],
                'amount'          => random_int(100, 1000),
                'description'     => "Expense description {$i}",
                'billing_month'   => date('Y-m', strtotime("-{$i} months")),
                'from_date'       => date('Y-m-d', strtotime("-{$i} days")),
                'to_date'         => date('Y-m-d', strtotime('-' . ($i - 1) . ' days')),
                'paid_by'         => $userIds[array_rand($userIds)],
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);

            $expenseId = $this->db->insertID();

            $minUsers = min(3, $userCount);
            $numUsers = random_int($minUsers, $userCount);

            $selectedKeys = array_rand($userIds, $numUsers);

            if (! is_array($selectedKeys)) {
                $selectedKeys = [$selectedKeys];
            }

            foreach ($selectedKeys as $key) {
                $expenseInvolvements[] = [
                    'expense_id' => $expenseId,
                    'user_id'    => $userIds[$key],
                    'created_at' => $now,
                ];
            }
        }

        if (! empty($expenseInvolvements)) {
            $this->db->table('expense_involvements')->insertBatch($expenseInvolvements);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new RuntimeException('Expense seeding failed.');
        }
    }
}