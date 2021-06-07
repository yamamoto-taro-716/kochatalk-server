<?php
use Migrations\AbstractMigration;

class CreateUsers extends AbstractMigration
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
        $table = $this->table('users');
        $table->addColumn("username", "string");
        $table->addColumn("password", "string");
        $table->addColumn("role", "integer", [
            "length" => 4,
            "default" => 0
        ]);
        $table->addColumn("acl", "string", [
            "null" => true
        ]);
        $table->addColumn('modified', 'datetime');
        $table->addColumn('created', 'datetime');
        $table->create();
    }
}
