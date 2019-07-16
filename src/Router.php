<?php

namespace Prim;

use FastRoute\{Dispatcher, RouteCollector};
use \Exception;

class Router
{
    /** @var RouteCollector */
    public $router;
    /** @var Dispatcher */
    public $dispatcher;
    /** @var Container */
    protected $container;

    protected $options = [];
    protected $routes = [];
    protected $currentGroupPrefix = '';

    public function __construct($container, array $options = [])
    {
        $this->container = $container;

        $this->options = $options += [
            'root' => '',
            'project_name' => '',
            'router_query_string' => true,
            'server' => $_SERVER
        ];

        $this->loadRoutes();

        $this->dispatcher = \FastRoute\CachedDispatcher(function(RouteCollector $router) {
            $this->buildRoutes($router);
            $this->router = $router;
        }, [
            'cacheFile' => "{$this->options['root']}/app/cache/route.cache",
            'cacheDisabled' => ($this->options['environment'] === 'dev'),
        ]);
    }

    public function loadRoutes()
    {
        include("{$this->options['root']}app/config/routing.php");
    }

    function dispatchRoute(): ?object
    {
        $httpMethod = $this->options['server']['REQUEST_METHOD'];
        $uri = $this->options['server']['REQUEST_URI'];

        if($this->options['router_query_string']) {
            $uri = parse_url($uri, PHP_URL_PATH);
        }

        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                echo $this->container->get('errorController')->handleError(404);
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                echo $this->container->get('errorController')->handleError(405, $allowedMethods);
                break;
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = array_values($routeInfo[2]);
                $method = $handler[1];

                $controller = $this->getController($handler[0]);

                $controller->$method(...$vars);
                break;
        }

        return $controller?? null;
    }

    protected function getController(string $controller): object
    {
        list($pack, $controller) = explode('\\', $controller);

        $controllerNamespace = "$pack\\Controller\\$controller";

        if(class_exists("{$this->options['project_name']}\\$controllerNamespace")) {
            $controllerNamespace = "{$this->options['project_name']}\\$controllerNamespace";
        } else if(!class_exists($controllerNamespace)) {
            throw new Exception("Can't find controller: $controllerNamespace");
        }

        return $this->fetchControllerFromContainer($controllerNamespace);
    }

    protected function fetchControllerFromContainer(string $controller): object
    {
        return $this->container->getController($controller);
    }

    public function getRoutesCount(): int
    {
        return array_sum(array_map("count", $this->routes));
    }

    function getRoutes(string $pack, string $routeFile = 'routing.php')
    {
        $included = false;

        if($vendorPath = $this->container->get('packlist')->getVendorPath($pack)) {
            $vendorFile = "{$this->options['root']}$vendorPath/config/$routeFile";

            if(file_exists($vendorFile)) {
                $included = true;
                include($vendorFile);
            }
        }

        $localFile = "{$this->options['root']}src/$pack/config/$routeFile";

        if(file_exists($localFile)) {
            $included = true;
            include($localFile);
        }

        if(!$included) throw new Exception("Can't find routes file $routeFile in $pack");

        return $this;
    }

    function get(string $route, string $controller, string $method): Router
    {
        $this->addRoute(['GET'], $route, $controller, $method);

        return $this;
    }

    function post(string $route, string $controller, string $method): Router
    {
        $this->addRoute(['POST'], $route, $controller, $method);

        return $this;
    }

    function both(string $route, string $controller, string $method): Router
    {
        $this->addRoute(['GET', 'POST'], $route, $controller, $method);

        return $this;
    }

    function addRoute(array $type, string $route, string $controller, string $method): Router
    {
        $route = $this->currentGroupPrefix . $route;

        foreach($type as $t) {
            $this->routes[$route][$t] = [$controller, $method];
        }

        return $this;
    }

    function addGroup(string $prefix, callable $callback): Router
    {
        $previousGroupPrefix = $this->currentGroupPrefix;
        $this->currentGroupPrefix = $previousGroupPrefix . $prefix;
        $callback($this);
        $this->currentGroupPrefix = $previousGroupPrefix;

        return $this;
    }

    function removeRoute(string $route): void
    {
        if(isset($this->routes[$route])) unset($this->routes[$route]);
    }

    function buildRoutes($router): void
    {
        foreach($this->routes as $uri => $types) {
            foreach($types as $type => $params) {
                list($controller, $method) = $params;
                $router->addRoute($type, $uri, [$controller, $method]);
            }
        }
    }
}
