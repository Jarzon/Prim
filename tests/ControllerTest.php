<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Prim\Controller;
use Tests\Mocks\Container;
use Tests\Mocks\View;

class ControllerTest extends TestCase
{
    public function testConstruct()
    {
        define('ROOT', '');
        define('DB_ENABLE', false);

        $view = new View();
        $container = new Container();
        $controller = new Controller($view, $container);

        $this->assertEquals('Prim', $controller->projectNamespace);
        $this->assertEquals('', $controller->packNamespace);

        return $controller;
    }

    /**
     * @depends testConstruct
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