<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAbsentDaysTable extends Migration
{
    public function up()
    {
        $fields = [
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'expense_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'days_absent' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 0,
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

        $this->forge->addKey('user_id');
        $this->forge->addKey('expense_id');
        $this->forge->addKey('created_at');

        $this->forge->addUniqueKey(['user_id', 'expense_id']);

        $this->forge->addForeignKey('user_id', 'users', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('expense_id', 'expenses', 'id', 'CASCADE', 'RESTRICT');

        $this->forge->createTable('absent_days');
    }

    public function down()
    {
        $this->forge->dropTable('absent_days');
    }
}
