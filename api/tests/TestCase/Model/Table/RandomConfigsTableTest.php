<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\RandomConfigsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\RandomConfigsTable Test Case
 */
class RandomConfigsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\RandomConfigsTable
     */
    public $RandomConfigs;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.random_configs'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('RandomConfigs') ? [] : ['className' => RandomConfigsTable::class];
        $this->RandomConfigs = TableRegistry::get('RandomConfigs', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->RandomConfigs);

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
}
