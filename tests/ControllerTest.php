<?php declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use Prim\View;
use Tests\Mocks\Controller;

class ControllerTest extends TestCase
{
    public function testControllerConstruct(): Controller
    {
        $view = $this->createMock(View::class);

        $controller = new Controller($view, []);

        $this->assertEquals('Tests', $controller->projectNamespace);
        $this->assertEquals('', $controller->packNamespace);

        return $controller;
    }

    #[Depends('testControllerConstruct')]
    public function testgetNamespace(Controller $controller)
    {
        $controller->getNamespace('\\Project\\TestPack\\Controller\\Test');

        $this->assertEquals('Project', $controller->projectNamespace);
        $this->assertEquals('TestPack', $controller->packNamespace);

        $controller->getNamespace('\\TestPack\\Controller\\Test');

        $this->assertEquals('', $controller->projectNamespace);
        $this->assertEquals('TestPack', $controller->packNamespace);
    }
}
