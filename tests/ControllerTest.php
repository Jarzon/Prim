<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tests\Mocks\Container;
use Tests\Mocks\Controller;
use Tests\Mocks\View;

class ControllerTest extends TestCase
{
    public function testControllerConstruct()
    {
        $serviceMock = new \Tests\Mocks\Service(null, ['project_name' => 'test']);

        $container = new Container([], [], $serviceMock);
        $view = new View($container, []);

        $controller = new Controller($view, []);

        $this->assertEquals('Tests', $controller->projectNamespace);
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
