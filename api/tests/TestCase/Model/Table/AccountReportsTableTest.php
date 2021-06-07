<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\AccountReportsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\AccountReportsTable Test Case
 */
class AccountReportsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\AccountReportsTable
     */
    public $AccountReports;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.account_reports',
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
        $config = TableRegistry::exists('AccountReports') ? [] : ['className' => AccountReportsTable::class];
        $this->AccountReports = TableRegistry::get('AccountReports', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->AccountReports);

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
