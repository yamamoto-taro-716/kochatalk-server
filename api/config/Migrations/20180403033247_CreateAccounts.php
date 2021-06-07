<?php
use Migrations\AbstractMigration;

class CreateAccounts extends AbstractMigration
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
        $table = $this->table('accounts');
        $table->addColumn("avatar", "string", [
            "null" => true
        ]);
        $table->addColumn("nickname", "string");
        $table->addColumn("intro", "string", [
            "null" => true,
            "length" => 3000,
        ]);
        $table->addColumn("age", "integer", [
            "null" => true,
            "default" => 0
        ]);
        $table->addColumn("gender", "integer", [
            "default" => 1,
            "length" => 4,
        ]);
        $table->addColumn("nationality", "string", [
            "null" => true,
            "length" => 5,
        ]);
        $table->addColumn("objective", "integer", [
            "null" => true,
            "length" => 4,
        ]);
        $table->addColumn("marital_status", "integer", [
            "null" => true,
            "length" => 1,
        ]);
        $table->addColumn("language_translate", "string", [
            "null" => true,
            "length" => 5,
        ]);
        $table->addColumn("memo", "text", [
            "null" => true
        ]);
        $table->addColumn("revision", "integer", [
            "default" => 1,
            "null" => true
        ]);
        $table->addColumn("device_id", "integer", [
            "null" => true
        ]);
        $table->addColumn("in_group", "integer", [
            "default" => 1,
            "length" => 4,
        ]);
        $table->addColumn("status", "integer", [
            "default" => 1,
            "length" => 4,
        ]);
	    $table->addColumn("flg_push", "boolean", [
		    "default" => true
	    ]);
        $table->addColumn('modified', 'datetime');
        $table->addColumn('created', 'datetime');

        $table->addIndex("nickname");
        $table->addIndex("age");
        $table->addIndex("nationality");
        $table->addIndex("objective");
        $table->addIndex("marital_status");
        $table->addIndex("device_id");
        $table->create();
    }
}
