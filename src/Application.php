<?php
namespace Prim;

use Phroute\Phroute\RouteCollector;
use Phroute\Phroute\Dispatcher;

class Application
{
    /**
     * Routing
     * @param string $method
     * @param string $url
     * @param object $error
     */
    public function __construct($method, $url, $error)
    {
        $router = new RouteCollector();

        include(APP . 'config/routing.php');

        // TODO : Cache $router->getData()
        $dispatcher = new Dispatcher($router->getData());

        try {
            $dispatcher->dispatch($method, parse_url($url, PHP_URL_PATH));
        } catch (\Exception $e) {
            echo $error->handleError($e);
        }
    }
}