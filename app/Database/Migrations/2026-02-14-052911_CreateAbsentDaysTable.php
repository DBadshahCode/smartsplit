<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAbsentDaysTable extends Migration
{
    public function up()
    {
        $this->forge->addField(['id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true,], 'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true,], 'month' => ['type' => 'VARCHAR', 'constraint' => 20,], 'days_absent' => ['type' => 'INT', 'constraint' => 3, 'default' => 0,], 'created_at' => ['type' => 'DATETIME', 'null' => true,], 'updated_at' => ['type' => 'DATETIME', 'null' => true,], 'deleted_at' => ['type' => 'DATETIME', 'null' => true,],]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('absent_days');
    }

    public function down()
    {
        $this->forge->dropTable('absent_days');
    }
}
