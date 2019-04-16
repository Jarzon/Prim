<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tests\Mocks\Container;

class Test {
    public $configured = false;
    private $view;

    public function __construct($view)
    {
        $this->view = $view;
    }

    public function setConfig() {
        $this->configured = true;
    }
}

class ContainerTest extends TestCase
{
    public function testRegister()
    {
        $container = new Container([
            'pdo.class' => '\Tests\Mocks\PDO',
            'service.class' => '\Tests\Mocks\Service'
        ], []);

        $container->register('test', \Tests\Test::class, [$container], function(Test $test) {
            $test->setConfig(); 
        });

        $this->assertIsObject($container->get('test'));
        $this->assertTrue($container->get('test')->configured);
    }

    public function testOpenDatabaseConnection()
    {
        $container = new Container([
            'pdo.class' => '\Tests\Mocks\PDO',
            'service.class' => '\Tests\Mocks\Service'
        ], [
            'db_enable' => true,
            'disable_services_injection' => true
        ]);

        $this->assertIsObject($container->getPDO());
    }
}