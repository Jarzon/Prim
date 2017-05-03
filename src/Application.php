<?php
namespace Prim;

class Application
{
    /**
     * @var Container $container
     * @var Controller $router
     */
    public $container;
    public $router;

    /**
     * Routing
     */
    public function __construct(Container $container, Controller $error)
    {
        $this->container = $container;

        if(ENV == 'prod') {
            define('URL_RELATIVE_BASE', $_SERVER['REQUEST_URI']);
            define('URL_BASE', '');
        }
        else {
            $dirname = str_replace('public', '', dirname($_SERVER['SCRIPT_NAME']));
            define('URL_RELATIVE_BASE', str_replace($dirname, '', $_SERVER['REQUEST_URI']));
            define('URL_BASE', $dirname);
        }

        define('URL_PROTOCOL', !empty($_SERVER['HTTPS'])? 'https://': 'http://');
        define('URL_DOMAIN', $_SERVER['SERVER_NAME']);

        define('URL', URL_PROTOCOL . URL_DOMAIN . URL_BASE);

        $dispatcher = \FastRoute\simpleDispatcher(function(\FastRoute\RouteCollector $router) {
            $this->router = $router;
            include(APP . 'config/routing.php');
        });

        $routeInfo = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], URL_RELATIVE_BASE);

        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                echo $error->handleError(404);
                break;
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                echo $error->handleError(405, $allowedMethods);
                break;
            case \FastRoute\Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = array_values($routeInfo[2]);

                $controller = $container->getController($handler[0]);
                $method = $handler[1];

                $controller->$method(...$vars);
                break;
        }
    }

    function getRoutes(string $pack, string $routeFile) {
        include(ROOT . "/src/$pack/config/$routeFile");
    }

    function get(string $route, string $controller, string $method) {
        $this->router->get($route, [$controller, $method]);
    }

    function post(string $route, string $controller, string $method) {
        $this->router->post($route, [$controller, $method]);
    }

    function addRoute(array $type, string $route, string $controller, string $method) {
        $this->router->addRoute($type, $route, [$controller, $method]);
    }

    function addGroup(string $prefix, callable $callback) {
        $this->router->addGroup($prefix, $callback);
    }
}