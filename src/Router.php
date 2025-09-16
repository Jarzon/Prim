<?php declare(strict_types=1);

namespace Prim;

use function FastRoute\CachedDispatcher;
use FastRoute\{Dispatcher, RouteCollector};
use \Exception;

class Router
{
    public RouteCollector $router;
    public Dispatcher $dispatcher;

    /** @var array<mixed> $options */
    protected array $options = [];
    /** @var array<mixed> $routes */
    protected array $routes = [];
    protected string $currentGroupPrefix = '';
    /** @var array<mixed> $currentRoute */
    public array $currentRoute = [];
    public string $currentUri = '';

    /** @param array<mixed> $options */
    public function __construct(public Container $container, array $options = [])
    {
        $this->options = $options += [
            'root' => '',
            'project_name' => '',
            'router_query_string' => true,
            'server' => $_SERVER
        ];

        $this->loadRoutes();

        $this->dispatcher = CachedDispatcher(function(RouteCollector $router) {
            $this->buildRoutes($router);
            $this->router = $router;
        }, [
            'cacheFile' => "{$this->options['root']}/app/cache/route.cache",
            'cacheDisabled' => ($this->options['environment'] === 'dev'),
        ]);
    }

    public function getCurrentController(): string|false
    {
        return $this->currentRoute[1][0] ?? false;
    }

    public function loadRoutes(): void
    {
        include("{$this->options['root']}app/config/routing.php");
    }

    function dispatchRoute(): ?object
    {
        $httpMethod = $this->options['server']['REQUEST_METHOD'];
        $uri = $this->options['server']['REQUEST_URI'];

        if($this->options['router_query_string']) {
            $uri = parse_url($uri, PHP_URL_PATH) ?: '';
        }

        if($uri === null) {
            echo $this->container->get('errorController')->handleError(400);
            exit;
        }

        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);

        $this->currentRoute = $routeInfo;
        $this->currentUri = $uri;

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

                $controllerNamespace = $this->getControllerNamespace($handler[0]);

                if(!empty($vars)) {
                    $reflector = new \ReflectionClass($controllerNamespace);

                    $parameters = $reflector->getMethod($method)->getParameters();

                    //Loop through each parameter and get the type
                    foreach($parameters as $key => $param)
                    {
                        if(!isset($vars[($key)])) continue;
                        // default value
                        if($vars[($key)] === '' && $param->isDefaultValueAvailable()) {
                            $vars[($key)] = $param->getDefaultValue();
                        }
                        else if($type = (string)$param->getType()) {
                            if(in_array($type, ['int', 'float', 'bool'])) {
                                settype($vars[($key)], $type);
                            }
                        }
                    }
                }

                $controller = $this->fetchControllerFromContainer($controllerNamespace);

                $controller->$method(...$vars);
                break;
        }

        return $controller?? null;
    }

    protected function getControllerNamespace(string $controller): string
    {
        list($pack, $controller) = explode('\\', $controller);

        $controllerNamespace = "$pack\\Controller\\$controller";

        if(class_exists("{$this->options['project_name']}\\$controllerNamespace")) {
            $controllerNamespace = "{$this->options['project_name']}\\$controllerNamespace";
        } else if(!class_exists($controllerNamespace)) {
            throw new Exception("Can't find controller: $controllerNamespace");
        }

        return $controllerNamespace;
    }

    protected function fetchControllerFromContainer(string $controller): object
    {
        return $this->container->getController($controller);
    }

    public function getRoutesCount(): int
    {
        return array_sum(array_map("count", $this->routes));
    }

    function registerRoutes(string $pack, string $routeFile = 'routing.php'): Router
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

    /** @param Array<string> $type */
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

    function buildRoutes(RouteCollector $router): void
    {
        foreach($this->routes as $uri => $types) {
            foreach($types as $type => $params) {
                list($controller, $method) = $params;
                $router->addRoute($type, $uri, [$controller, $method]);
            }
        }
    }
}
