<?php

namespace Prim;

class Router
{
    public $router = '';
    protected $routes = [];
    protected $currentGroupPrefix = '';

    public function __construct($router)
    {
        $this->router = $router;

        include(APP . 'config/routing.php');

        $this->buildRoutes();
    }

    function getRoutes(string $pack, string $routeFile)
    {
        include($this->getRoutesFilePath($pack, $routeFile));
    }

    protected function getRoutesFilePath($packDirectory, $file) {
        $localFile = ROOT . "src/$packDirectory/config/$file";
        $vendorFile = ROOT . "vendor/".strtolower($packDirectory)."/config/$file";

        if(file_exists($vendorFile)) {
            return $vendorFile;
        }
        elseif(file_exists($localFile)) {
            return $localFile;
        }

        throw new \Exception("Can't find routes file $file in $packDirectory");
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
        $route = $this->currentGroupPrefix . $route;

        foreach($type as $t) {
            $this->routes[$route][$t] = [$controller, $method];
        }
    }

    function addGroup(string $prefix, callable $callback)
    {
        $previousGroupPrefix = $this->currentGroupPrefix;
        $this->currentGroupPrefix = $previousGroupPrefix . $prefix;
        $callback($this);
        $this->currentGroupPrefix = $previousGroupPrefix;
    }

    function removeRoute(string $route) {
        if(isset($this->routes[$route])) unset($this->routes[$route]);
    }

    function buildRoutes() {
        foreach($this->routes as $uri => $types) {
            foreach($types as $type => $params) {
                list($controller, $method) = $params;
                $this->router->addRoute($type, $uri, [$controller, $method]);
            }
        }
    }
}