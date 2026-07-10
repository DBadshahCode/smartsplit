<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateChapatiExtraInvolvementTable extends Migration
{
    public function up()
    {
        $fields = [
            'extra_expense_id' => ['type' => 'BIGINT', 'unsigned' => true,],
            'user_id' => ['type' => 'BIGINT', 'unsigned' => true,],
            'created_at' => ['type' => 'DATETIME', 'null' => true,],
            'updated_at' => ['type' => 'DATETIME', 'null' => true,],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true,],
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
        $this->forge->addUniqueKey([
            'chapati_extra_expense_id',
            'user_id',
        ]);
        $this->forge->addForeignKey('extra_expense_id', 'chapati_extra_expenses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('chapati_extra_involvements');
    }

    public function down()
    {
        $this->forge->dropTable('chapati_extra_involvements', true);
    }
}
