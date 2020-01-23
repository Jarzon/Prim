<?php declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Prim\Container;
use Prim\PackList;
use Prim\Service;
use Tests\Pack\Controller\Controller;

class Test {
    public $configured = false;
    public $nothing = false;
    private $view;

    public function __construct($view, $nothing = false)
    {
        $this->view = $view;
        $this->nothing = $nothing;
    }

    public function setConfig() {
        $this->configured = true;
    }
}

class ContainerTest extends TestCase
{
    public function testConstructor()
    {
        $service = $this->createMock(Service::class);
        $packlist = $this->createMock(PackList::class);

        $service
            ->method('getPackList')
            ->willReturn($packlist);

        $controller = $this->createMock(Controller::class);

        $container = new Container([
            'project_name' => 'Tests'
        ], [
            'errorController' => $controller
        ], $service);

        $this->assertIsObject($container);

        return $container;
    }

    /**
     * @depends testConstructor
     */
    public function testRegister(Container $container)
    {
        $container->register('test', \Tests\Test::class, function(Container $container) {
            $test = $container->init('test', [$container]);

            $test->setConfig();

            return $test;
        });

        $this->assertIsObject($container->get('test'));
        $this->assertTrue($container->get('test')->configured);
        $this->assertFalse($container->get('test')->nothing);
    }

    /**
     * @depends testConstructor
     */
    public function testErrorController(Container $container)
    {
        $errorController = $container->get('errorController');

        $this->assertIsObject($errorController);
    }
}
