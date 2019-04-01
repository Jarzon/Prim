<?php
namespace Tests\Mocks;

class Router extends \Prim\Router
{
    protected function fetchControllerFromContainer(string $controller): object
    {

        return new class() {
            public $methodCalled = false;

            function aMethod()
            {
                $this->methodCalled = true;
            }
        };
    }
}