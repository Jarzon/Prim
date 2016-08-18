<?php
namespace Prim\Core;

// TODO : INJECTION but useless without a interface to use?
use Phroute\Phroute\RouteCollector;
use Phroute\Phroute\Dispatcher;

class Application
{
    /**
     * Routing
     */
    public function __construct()
    {
        $router = new RouteCollector();

        include(APP . 'config/routing.php');

        // TODO : Cache $router->getData(), probably with APC
        $dispatcher = new Dispatcher($router->getData());

        $uri = str_replace(URL_SUB_FOLDER, '', $_SERVER['REQUEST_URI']);

        $response = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], parse_url($uri, PHP_URL_PATH));

        // Print out the value returned from the dispatched function
        echo $response;
    }
}
