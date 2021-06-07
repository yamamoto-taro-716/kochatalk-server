<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\AccountFriendsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\AccountFriendsTable Test Case
 */
class AccountFriendsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\AccountFriendsTable
     */
    public $AccountFriends;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.account_friends',
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
        $config = TableRegistry::exists('AccountFriends') ? [] : ['className' => AccountFriendsTable::class];
        $this->AccountFriends = TableRegistry::get('AccountFriends', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->AccountFriends);

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
