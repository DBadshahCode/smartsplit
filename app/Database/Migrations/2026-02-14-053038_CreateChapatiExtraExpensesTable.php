<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateChapatiExtraExpensesTable extends Migration
{
    public function up()
    {
        $this->forge->addField(['id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true,], 'chapati_expense_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true,], 'item' => ['type' => 'VARCHAR', 'constraint' => 100,], 'amount' => ['type' => 'FLOAT', 'constraint' => '10,2', 'default' => 0.00,], 'created_at' => ['type' => 'DATETIME', 'null' => true,], 'updated_at' => ['type' => 'DATETIME', 'null' => true,], 'deleted_at' => ['type' => 'DATETIME', 'null' => true,],]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('chapati_expense_id', 'chapati_expenses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('chapati_extra_expenses');
    }

    public function down()
    {
        $this->forge->dropTable('chapati_extra_expenses');
    }
}
