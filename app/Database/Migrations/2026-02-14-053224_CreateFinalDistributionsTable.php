<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFinalDistributionsTable extends Migration
{
    public function up()
    {
        $fields = [
            'user_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],
            'month' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'expenses_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'due_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'advance_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'final_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'generated_at' => [
                'type' => 'DATETIME',
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

        $this->forge->addKey('user_id');
        $this->forge->addKey('month');

        $this->forge->addUniqueKey(['user_id', 'month']);

        $this->forge->addForeignKey('user_id', 'users', 'id', 'RESTRICT', 'RESTRICT');

        $this->forge->createTable('final_distributions');
    }

    public function down()
    {
        $this->forge->dropTable('final_distributions', true);
    }
}
