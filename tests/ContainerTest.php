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
    public function testConstructor()
    {
        $conf = [
            'project_name' => 'test',
            'pdo.class' => '\Tests\Mocks\PDO',
            'service.class' => '\Tests\Mocks\Service'
        ];

        $serviceMock = new \Tests\Mocks\Service(null, $conf);

        $container = new Container([], [
            'pdo.class' => '\Tests\Mocks\PDO',
            'service.class' => '\Tests\Mocks\Service'
        ], $serviceMock);

        $this->assertIsObject($container);

        return $container;
    }

    /**
     * @depends testConstructor
     */
    public function testRegister(Container $container)
    {
        $container->register('test', \Tests\Test::class, function($container) {
            $test = $container->init('test', [$container]);

            $test->setConfig();

            return $test;
        });

        $this->assertIsObject($container->get('test'));
        $this->assertTrue($container->get('test')->configured);
    }
}