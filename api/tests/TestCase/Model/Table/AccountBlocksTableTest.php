<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\AccountBlocksTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\AccountBlocksTable Test Case
 */
class AccountBlocksTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\AccountBlocksTable
     */
    public $AccountBlocks;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.account_blocks',
        'app.account_actions',
        'app.account_receives'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('AccountBlocks') ? [] : ['className' => AccountBlocksTable::class];
        $this->AccountBlocks = TableRegistry::get('AccountBlocks', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->AccountBlocks);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
