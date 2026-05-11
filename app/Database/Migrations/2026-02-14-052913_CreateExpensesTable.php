<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateExpensesTable extends Migration
{
    public function up()
    {
        $fields = [
            'expense_type_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'description' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'amount' => [
                'type' => 'FLOAT',
                'constraint' => '10,2',
                'default' => 0.00,
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
                'type' => 'INT',
                'constraint' => 11,
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
        $this->forge->addField('id');
        $this->forge->addField($fields);

        $this->forge->addKey('expense_type_id');
        $this->forge->addKey('from_date');
        $this->forge->addKey('to_date');

        $this->forge->addForeignKey('expense_type_id', 'expense_types', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('paid_by', 'users', 'id', 'SET NULL', 'RESTRICT');

        $this->forge->createTable('expenses');
    }

    public function down()
    {
        $this->forge->dropTable('expenses');
    }
}
