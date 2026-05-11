<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateExpenseInvolvementsTable extends Migration
{
    public function up()
    {
        $fields = [
            'expense_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'amount' => [
                'type' => 'FLOAT',
                'constraint' => '10,2',
                'default' => 0.00,
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

        $this->forge->addKey('expense_id');
        $this->forge->addKey('user_id');

        $this->forge->addUniqueKey(['expense_id', 'user_id']);

        $this->forge->addForeignKey('expense_id', 'expenses', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'RESTRICT', 'RESTRICT');

        $this->forge->createTable('expense_involvements');
    }

    public function down()
    {
        $this->forge->dropTable('expense_involvements');
    }
}
