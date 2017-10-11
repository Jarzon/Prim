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
    public $projectNamespace;

    /**
     * Routing
     */
    public function __construct(Container $container, Controller $error)
    {
        $this->container = $container;

        $this->projectNamespace = $error->projectNamespace;

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

        $dispatcher = \FastRoute\cachedDispatcher(function(\FastRoute\RouteCollector $router) {
            $this->router = $this->container->getRouter($router);
        }, [
            'cacheFile' => ROOT . '/app/cache/route.cache',
            'cacheDisabled' => (ENV === 'dev'),
        ]);

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

                list($pack, $controller) = explode('\\', $handler[0]);

                $namespace = '';

                if(file_exists(ROOT . "src/$pack/Controller/$controller.php")) {
                    $namespace = $this->projectNamespace.'\\';
                }

                $namespace .= "$pack\\Controller\\$controller";

                $controller = $container->getController($namespace);
                $method = $handler[1];

                $controller->$method(...$vars);
                break;
        }
    }
}