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
        $container = new Container([], [
            'pdo.class' => '\Tests\Mocks\PDO',
            'service.class' => '\Tests\Mocks\Service'
        ]);

        $container->register('test', \Tests\Test::class, function($container) {
            $test = $container->init('test', [$container]);

            $test->setConfig();

            return $test;
        });

        $this->assertIsObject($container->get('test'));
        $this->assertTrue($container->get('test')->configured);
    }

    public function testOpenDatabaseConnection()
    {
        $container = new Container([
            'db_enable' => true,
            'disable_services_injection' => true
        ], [
            'pdo.class' => '\Tests\Mocks\PDO',
            'service.class' => '\Tests\Mocks\Service'
        ]);

        $this->assertIsObject($container->get('pdo'));
    }
}