<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    public function run()
    {
        // First, create some expenses        $expenses = [];
        for ($i = 1; $i <= 20; $i++) {
            $expenses[] = [
                'expense_type_id' => ($i % 5) + 1, // Assuming there are 5 expense types
                'amount' => rand(100, 1000),
                'description' => "Expense description $i",
                'billing_month' => date('Y-m', strtotime("-$i months")),
                'from_date' => date('Y-m-d', strtotime("-$i days")),
                'to_date' => date('Y-m-d', strtotime("-" . ($i - 1) . " days")),
                'paid_by' => ($i % 5) + 1, // Assign users in a round-robin fashion
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        $this->db->table('expenses')->insertBatch($expenses);

        // Now create ExpenseInvolvements for each expense and has mostly all users involved in each expense some of the expenses will have 7-8 users involved in it
        $expenseInvolvements = [];
        for ($i = 1; $i <= 20; $i++) {
            $numInvolvedUsers = rand(3, 8); // Each expense will have between 3 and 8 involved users
            for ($j = 1; $j <= $numInvolvedUsers; $j++) {
                $expenseInvolvements[] = [
                    'expense_id' => $i,
                    'user_id' => (($i + $j) % 5) + 1, // Assign users in a round-robin fashion, offset by j
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }
        }

        $this->db->table('expense_involvements')->insertBatch($expenseInvolvements);
    }
}
