<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMonthColumnInExpensesTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('expenses', [
            'billing_month' => [
                'type' => 'VARCHAR',
                'constraint' => 7,
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('expenses', 'billing_month');
    }
}
