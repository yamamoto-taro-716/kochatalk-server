<?php

use Migrations\AbstractMigration;

class CreateDevices extends AbstractMigration
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
        $table = $this->table('devices');
        $table->addColumn('uuid', 'string', [
            'length' => 64,
            'null' => false
        ]);
        $table->addColumn('user_agent', 'string', [
            'length' => 10,
            'null' => false
        ]);
        $table->addColumn('push_token', 'string', [
            'length' => 255,
            'null' => true
        ]);
        $table->addColumn('version', 'string', [
            'length' => 10,
            'null' => false,
        ]);
        $table->addColumn('last_access', 'datetime');
        $table->addColumn('created', 'datetime');

        $table->addIndex('uuid');
        $table->addIndex(["id", "last_access"], [
            "name" => "id_last_access_idx"
        ]);
        $table->create();
    }
}
