<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRecoveryCodeToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'recovery_code_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'password',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'recovery_code_hash');
    }
}
