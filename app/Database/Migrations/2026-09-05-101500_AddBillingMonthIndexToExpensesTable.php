<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * `billing_month` was added to `expenses` without an index, but every
 * month-scoped read filters on it: generating a distribution, exporting the
 * workbook and listing a month's expenses. Without the index each of those
 * scans the whole table.
 */
class AddBillingMonthIndexToExpensesTable extends Migration
{
    private const KEY_NAME = 'expenses_billing_month';

    public function up()
    {
        $this->forge->addKey('billing_month', false, false, self::KEY_NAME);
        $this->forge->processIndexes('expenses');
    }

    public function down()
    {
        $this->forge->dropKey('expenses', self::KEY_NAME);
    }
}
