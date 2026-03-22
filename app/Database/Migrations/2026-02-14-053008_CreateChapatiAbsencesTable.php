<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateChapatiAbsencesTable extends Migration
{
    public function up()
    {
        $this->forge->addField(['id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true,], 'chapati_expense_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true,], 'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true,], 'days_absent' => ['type' => 'INT', 'constraint' => 3, 'default' => 0,], 'created_at' => ['type' => 'DATETIME', 'null' => true,], 'updated_at' => ['type' => 'DATETIME', 'null' => true,], 'deleted_at' => ['type' => 'DATETIME', 'null' => true,],]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('chapati_expense_id', 'chapati_expenses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('chapati_absences');
    }

    public function down()
    {
        $this->forge->dropTable('chapati_absences');
    }
}
