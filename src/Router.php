<?php

namespace Prim;

class Router
{
    public $router = '';
    protected $routes = [];

    public function __construct($router)
    {
        $this->router = $router;
        include(APP . 'config/routing.php');
        $this->buildRoutes();
    }

    function getRoutes(string $pack, string $routeFile)
    {
        $included = false;
        if(file_exists(ROOT . "vendor/$pack/config/$routeFile")) {
            $included = true;
            include(ROOT . "vendor/$pack/config/$routeFile");
        }

        if(file_exists(ROOT . "src/$pack/config/$routeFile")) {
            $included = true;
            include(ROOT . "src/$pack/config/$routeFile");
        }

        if(!$included) {
            throw new \Exception("Can't find $routeFile in $pack");
        }
    }

    function get(string $route, string $controller, string $method)
    {
        $this->addRoute(['GET'], $route, $controller, $method);
    }

    function post(string $route, string $controller, string $method)
    {
        $this->addRoute(['POST'], $route, $controller, $method);
    }

    function both(string $route, string $controller, string $method)
    {
        $this->addRoute(['GET', 'POST'], $route, $controller, $method);
    }

    function addRoute(array $type, string $route, string $controller, string $method)
    {
        $this->routes[$route] = [$type, $controller, $method];
    }

    function addGroup(string $prefix, callable $callback)
    {
        $this->router->addGroup($prefix, $callback);
    }

    function buildRoutes() {
        foreach($this->routes as $uri => $params) {
            list($type, $controller, $method) = $params;
            $this->router->addRoute($type, $uri, [$controller, $method]);
        }
    }
}