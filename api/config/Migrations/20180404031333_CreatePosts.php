<?php
use Migrations\AbstractMigration;

class CreatePosts extends AbstractMigration
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
        $table = $this->table('posts', [
            "id" => false,
            "primary_key" => "id"
        ]);
        $table->addColumn("id", "biginteger", [
            "signed" => false,
            "autoIncrement" => true
        ]);
        $table->addColumn("account_id", "integer");
        $table->addColumn("content", "text", [
            "null" => true
        ]);
        $table->addColumn("images", "string", [
            "null" => true,
            "length" => 1000
        ]);
        $table->addColumn("revision", "integer", [
            "default" => 1,
            "null" => true
        ]);
        $table->addColumn("status", "integer", [
            "default" => 0,
            "length" => 4,
            "null" => true
        ]);
        $table->addColumn('modified', 'datetime');
        $table->addColumn('created', 'datetime');

        $table->addIndex("account_id");
        $table->addIndex(["id", "modified"], [
            "name" => "id_modified_idx"
        ]);
        $table->create();
    }
}
