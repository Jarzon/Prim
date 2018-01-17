<?php

namespace Prim;

class Router
{
    public $router = '';
    protected $container;
    protected $routes = [];
    protected $currentGroupPrefix = '';

    /**
     * @param Container $container
     */
    public function __construct(\FastRoute\RouteCollector $router, $container)
    {
        $this->router = $router;
        $this->container = $container;

        include(APP . 'config/routing.php');

        $this->buildRoutes();
    }

    function getRoutesCount()
    {
        return count($this->routes);
    }

    function getRoutes(string $pack, string $routeFile)
    {
        $included = false;

        if($vendorPath = $this->container->getPackList()->getVendorPath($pack)) {
            $vendorFile = ROOT . "$vendorPath/config/$routeFile";

            if(file_exists($vendorFile)) {
                $included = true;
                include($vendorFile);
            }
        }

        $localFile = ROOT . "src/$pack/config/$routeFile";

        if(file_exists($localFile)) {
            $included = true;
            include($localFile);
        }

        if(!$included) throw new \Exception("Can't find routes file $routeFile in $pack");
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