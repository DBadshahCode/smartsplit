<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFinalDistributionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField(
            [
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true,],
                'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true,],
                'month' => ['type' => 'VARCHAR', 'constraint' => 20,],
                'chapati_amount' => ['type' => 'FLOAT', 'constraint' => '10,2', 'default' => 0.00,],
                'other_expenses_amount' => ['type' => 'FLOAT', 'constraint' => '10,2', 'default' => 0.00,],
                'due_amount' => ['type' => 'FLOAT', 'constraint' => '10,2', 'default' => 0.00,],
                'advance_amount' => ['type' => 'FLOAT', 'constraint' => '10,2', 'default' => 0.00,],
                'final_amount' => ['type' => 'FLOAT', 'constraint' => '10,2', 'default' => 0.00,],
                'generated_at' => ['type' => 'DATETIME', 'null' => true,],
                'created_at' => ['type' => 'DATETIME', 'null' => true,],
                'updated_at' => ['type' => 'DATETIME', 'null' => true,],
                'deleted_at' => ['type' => 'DATETIME', 'null' => true,],
            ]
        );
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('final_distributions');
    }

    public function down()
    {
        $this->forge->dropTable('final_distributions');
    }
}
