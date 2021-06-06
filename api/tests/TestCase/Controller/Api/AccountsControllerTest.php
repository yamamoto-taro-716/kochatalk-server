<?php
namespace App\Test\TestCase\Controller\Api;

use App\Controller\Api\AccountsController;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\Api\AccountsController Test Case
 */
class AccountsControllerTest extends IntegrationTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.accounts',
        'app.devices'
    ];

    /**
     * Test initial setup
     *
     * @return void
     */
    public function testInitialization()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
