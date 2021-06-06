<?php

use Migrations\AbstractMigration;

class CreateAccountReports extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $table = $this->table('account_reports', [
            "id" => false,
            "primary_key" => "id"
        ]);
        $table->addColumn("id", "biginteger", [
            "autoIncrement" => true
        ]);
        $table->addColumn("account_action_id", "integer");
        $table->addColumn("account_receive_id", "integer");
        $table->addColumn("status", "integer", [
            "default" => 0,
            "length" => 4
        ]);
        $table->addColumn('modified', 'datetime');
        $table->addColumn('created', 'datetime');

        $table->addIndex("account_action_id");
        $table->addIndex("account_receive_id");
        $table->create();
    }
}
