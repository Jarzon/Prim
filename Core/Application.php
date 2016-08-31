<?php
namespace Prim\Core;

// TODO : INJECTION but without a interface to use?
use Phroute\Phroute\RouteCollector;
use Phroute\Phroute\Dispatcher;

class Application
{
    /**
     * Routing
     */
    public function __construct($method, $url)
    {
        $router = new RouteCollector();

        include(APP . 'config/routing.php');

        // TODO : Cache $router->getData()
        $dispatcher = new Dispatcher($router->getData());

        $response = $dispatcher->dispatch($method, parse_url($url, PHP_URL_PATH));

        // Print out the value returned from the dispatched function
        echo $response;
    }
}