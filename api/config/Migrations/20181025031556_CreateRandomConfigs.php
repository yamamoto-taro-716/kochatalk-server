<?php
use Migrations\AbstractMigration;

class CreateRandomConfigs extends AbstractMigration
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
        $table = $this->table('random_configs');
        $table->addColumn("title", "string");
        $table->addColumn("created_type", "integer", [
        	"default" => 1,
        	"length" => 4,
        ]);
        $table->addColumn("created_value", "string", [
            "null" => true
        ]);
        $table->addColumn("access_type", "integer", [
	        "default" => 1,
	        "length" => 4,
        ]);
	    $table->addColumn("access_value", "string", [
		    "null" => true
	    ]);
	    $table->addColumn("random_limit", "integer");
        $table->create();
    }
}
