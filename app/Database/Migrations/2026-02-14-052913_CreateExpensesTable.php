<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateExpensesTable extends Migration
{
    public function up()
    {
        $this->forge->addField(['id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true,], 'expense_type_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true,], 'amount' => ['type' => 'FLOAT', 'constraint' => '10,2', 'default' => 0.00,], 'from_date' => ['type' => 'DATE', 'null' => false,], 'to_date' => ['type' => 'DATE', 'null' => false,], 'paid_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true,], 'created_at' => ['type' => 'DATETIME', 'null' => true,], 'updated_at' => ['type' => 'DATETIME', 'null' => true,], 'deleted_at' => ['type' => 'DATETIME', 'null' => true,],]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('expense_type_id', 'expense_types', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('paid_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('expenses');
    }

    public function down()
    {
        $this->forge->dropTable('expenses');
    }
}
