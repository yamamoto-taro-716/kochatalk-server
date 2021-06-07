<?php
use Migrations\AbstractMigration;

class CreateSettings extends AbstractMigration
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
        $table = $this->table('settings');
        $table->addColumn("day_message", "integer", [
            "null" => true,
            "default" => 0,
            "length" => 4
        ]);
        $table->addColumn("count_ads", "integer", [
            "null" => true,
            "default" => 0,
            "length" => 4
        ]);
	    $table->addColumn("title_ads", "text", [
		    "null" => true,
	    ]);
	    $table->addColumn("title_ads_en", "text", [
		    "null" => true,
	    ]);
	    $table->addColumn("show_notify", "boolean", [
	    	"default" => true
	    ]);
        $table->addColumn("content_ads", "text", [
            "null" => true,
        ]);
	    $table->addColumn("content_ads_en", "text", [
		    "null" => true,
	    ]);
        $table->addColumn("term_ja", "text", [
            "null" => true,
        ]);
        $table->addColumn("term_en", "text", [
            "null" => true,
        ]);
        $table->addColumn("policy_ja", "text", [
            "null" => true,
        ]);
        $table->addColumn("policy_en", "text", [
            "null" => true,
        ]);
        $table->create();
    }
}
