<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateChapatiExpensesTable extends Migration
{
    public function up()
    {
        $fields = [
            'expense_type_id' => ['type' => 'BIGINT', 'unsigned' => true,],
            'from_date' => ['type' => 'DATE', 'null' => false,],
            'to_date' => ['type' => 'DATE', 'null' => false,],
            'total_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => '0.00',
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true,],
            'updated_at' => ['type' => 'DATETIME', 'null' => true,],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true,],
        ];
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
        ]);

        $this->forge->addField($fields);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('expense_type_id', 'expense_types', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('chapati_expenses');
    }

    public function down()
    {
        $this->forge->dropTable('chapati_expenses', true);
    }
}
