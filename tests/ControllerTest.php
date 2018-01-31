<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Prim\Controller;
use Tests\Mocks\Container;

class ControllerTest extends TestCase
{
    public function testControllerConstruct()
    {
        $container = new Container([
            'view.class' => '\Tests\Mocks\View',
            'router.class' => '\\Prim\\Router',
            'pdo.class' => '\Tests\Mocks\PDO'
        ], ['root' => 'vfs://root/']);

        $view = $container->getView();
        $controller = new Controller($view, $container, []);

        $this->assertEquals('Prim', $controller->projectNamespace);
        $this->assertEquals('', $controller->packNamespace);

        return $controller;
    }

    /**
     * @depends testControllerConstruct
     */
    public function testgetNamespace($controller)
    {
        $controller->getNamespace('\\Project\\TestPack\\Controller\\Test');

        $this->assertEquals('Project', $controller->projectNamespace);
        $this->assertEquals('TestPack', $controller->packNamespace);

        $controller->getNamespace('\\TestPack\\Controller\\Test');

        $this->assertEquals('', $controller->projectNamespace);
        $this->assertEquals('TestPack', $controller->packNamespace);
    }
}