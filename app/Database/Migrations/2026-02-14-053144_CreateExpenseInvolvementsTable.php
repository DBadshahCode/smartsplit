<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateExpenseInvolvementsTable extends Migration
{
    public function up()
    {
        $this->forge->addField(['id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true,], 'expense_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true,], 'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true,], 'created_at' => ['type' => 'DATETIME', 'null' => true,], 'updated_at' => ['type' => 'DATETIME', 'null' => true,], 'deleted_at' => ['type' => 'DATETIME', 'null' => true,],]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('expense_id', 'expenses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('expense_involvements');
    }

    public function down()
    {
        $this->forge->dropTable('expense_involvements');
    }
}
