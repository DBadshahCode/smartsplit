<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateExpensesTable extends Migration
{
    public function up()
    {
        $fields = [
            'expense_type_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],
            'description' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => '0.00',
            ],
            'from_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'to_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'paid_by' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
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

        $this->forge->addKey('expense_type_id');
        $this->forge->addKey([
            'from_date',
            'to_date',
        ]);
        $this->forge->addKey('paid_by');

        $this->forge->addForeignKey('expense_type_id', 'expense_types', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('paid_by', 'users', 'id', 'SET NULL', 'RESTRICT');

        $this->forge->createTable('expenses');
    }

    public function down()
    {
        $this->forge->dropTable('expenses', true);
    }
}
