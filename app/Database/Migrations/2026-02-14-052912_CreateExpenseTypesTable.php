<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateExpenseTypesTable extends Migration
{
    public function up()
    {
        $this->forge->addField(['id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true,], 'name' => ['type' => 'VARCHAR', 'constraint' => 100,], 'description' => ['type' => 'TEXT', 'null' => true,], 'split_method' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true,], 'is_active' => ['type' => 'BOOLEAN', 'default' => true,], 'created_at' => ['type' => 'DATETIME', 'null' => true,], 'updated_at' => ['type' => 'DATETIME', 'null' => true,], 'deleted_at' => ['type' => 'DATETIME', 'null' => true,],]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('expense_types');
    }

    public function down()
    {
        $this->forge->dropTable('expense_types');
    }
}
