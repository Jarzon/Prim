<?php

namespace Prim;

class Router
{
    /** @var \FastRoute\RouteCollector */
    public $router;
    /** @var \FastRoute\Dispatcher */
    public $dispatcher;

    protected $container;
    protected $options = [];
    protected $routes = [];
    protected $currentGroupPrefix = '';

    /**
     * @param Container $container
     */
    public function __construct($container, array $options = [])
    {
        $this->container = $container;

        $this->options = $options += [
            'root' => '',
            'router_query_string' => true
        ];

        include("{$this->options['root']}app/config/routing.php");

        $this->dispatcher = \FastRoute\CachedDispatcher(function(\FastRoute\RouteCollector $router) {
            $this->router = $router;
        }, [
            'cacheFile' => "{$this->options['root']}/app/cache/route.cache",
            'cacheDisabled' => ($this->options['environment'] === 'dev'),
        ]);

        $this->buildRoutes();
    }

    function dispatchRoute() {
        $httpMethod = $this->options['server']['REQUEST_METHOD'];
        $uri = $this->options['server']['REQUEST_URI'];

        if($this->options['router_query_string']) {
            $uri = parse_url($uri, PHP_URL_PATH);
        }

        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);

        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                echo $this->container->getErrorController()->handleError(404);
                break;
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                echo $this->container->getErrorController()->handleError(405, $allowedMethods);
                break;
            case \FastRoute\Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = array_values($routeInfo[2]);

                list($pack, $controller) = explode('\\', $handler[0]);

                $controllerNamespace = "$pack\\Controller\\$controller";

                if(class_exists("{$this->options['project_name']}\\$controllerNamespace")) {
                    $controllerNamespace = "{$this->options['project_name']}\\$controllerNamespace";
                } else if(!class_exists($controllerNamespace)) {
                    throw new \Exception("Can't find controller: $controllerNamespace");
                }

                $controller = $this->container->getController($controllerNamespace);
                $method = $handler[1];

                $controller->$method(...$vars);
                break;
        }
    }

    function getRoutesCount() : int
    {
        return array_sum(array_map("count", $this->routes));
    }

    function getRoutes(string $pack, string $routeFile) : void
    {
        $included = false;

        if($vendorPath = $this->container->getPackList()->getVendorPath($pack)) {
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

        if(!$included) throw new \Exception("Can't find routes file $routeFile in $pack");
    }

    function get(string $route, string $controller, string $method) : void
    {
        $this->addRoute(['GET'], $route, $controller, $method);
    }

    function post(string $route, string $controller, string $method) : void
    {
        $this->addRoute(['POST'], $route, $controller, $method);
    }

    function both(string $route, string $controller, string $method) : void
    {
        $this->addRoute(['GET', 'POST'], $route, $controller, $method);
    }

    function addRoute(array $type, string $route, string $controller, string $method) : void
    {
        $route = $this->currentGroupPrefix . $route;

        foreach($type as $t) {
            $this->routes[$route][$t] = [$controller, $method];
        }
    }

    function addGroup(string $prefix, callable $callback) : void
    {
        $previousGroupPrefix = $this->currentGroupPrefix;
        $this->currentGroupPrefix = $previousGroupPrefix . $prefix;
        $callback($this);
        $this->currentGroupPrefix = $previousGroupPrefix;
    }

    function removeRoute(string $route) : void
    {
        if(isset($this->routes[$route])) unset($this->routes[$route]);
    }

    function buildRoutes() : void
    {
        foreach($this->routes as $uri => $types) {
            foreach($types as $type => $params) {
                list($controller, $method) = $params;
                $this->router->addRoute($type, $uri, [$controller, $method]);
            }
        }
    }
}