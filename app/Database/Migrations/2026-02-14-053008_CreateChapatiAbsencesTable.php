<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateChapatiAbsencesTable extends Migration
{
    public function up()
    {
        $fields = [
            'chapati_expense_id' => ['type' => 'BIGINT', 'unsigned' => true,],
            'user_id' => ['type' => 'BIGINT', 'unsigned' => true,],
            'days_absent' => ['type' => 'INT', 'constraint' => 3, 'default' => 0,],
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
        $this->forge->addForeignKey('chapati_expense_id', 'chapati_expenses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('chapati_absences');
    }

    public function down()
    {
        $this->forge->dropTable('chapati_absences', true);
    }
}
