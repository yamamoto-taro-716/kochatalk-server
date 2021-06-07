<?php
use Migrations\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class UpdateAccount extends AbstractMigration
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
        $table->addColumn('prefecture', 'string', [
            'null' => true,
            'limit' => 256,
            'after' => 'gender'
        ]);
        $table->addColumn('avatar_status', "integer", [
            'default' => 0,
            'limit' => MysqlAdapter::INT_TINY,
            'after' => 'status'
        ]);
        $table->update();
    }
}
