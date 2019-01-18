<?php
namespace Prim;

use \PDO;

class Application
{
    public $container;

    protected $options = [];

    /**
     * @param Container $container
     */
    public function __construct($container, array $options = [])
    {
        $this->options = $options += [
            'root' => '',
            'project_name' => '',
            'db_enable' => false,
            'db_options' => [],
            'router_query_string' => false
        ];

        $this->container = $container;

        $this->setErrorHandlers();

        $this->openDatabaseConnection($options['db_type'], $options['db_host'], $options['db_name'], $options['db_charset'], $options['db_user'], $options['db_password'], $options['db_options'], $options['db_enable']);

        $dispatcher = \FastRoute\cachedDispatcher(function(\FastRoute\RouteCollector $router) {
            $this->container->getRouter($router);
        }, [
            'cacheFile' => "{$this->options['root']}/app/cache/route.cache",
            'cacheDisabled' => ($options['environment'] === 'dev'),
        ]);

        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        if($options['router_query_string']) $uri = parse_url($uri, PHP_URL_PATH);

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);

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

                $controller = $container->getController($controllerNamespace);
                $method = $handler[1];

                $controller->$method(...$vars);
                break;
        }
    }

    public function setErrorHandlers() : void
    {
        register_shutdown_function( [$this, 'checkFatal'] );
        set_error_handler( [$this, 'logError'] );
        set_exception_handler( [$this, 'logException'] );
    }

    public function openDatabaseConnection(string $type, string $host, string $name, string $user, string $pass, array $options, string $charset = 'utf8', bool $enable = false) : void
    {
        if($enable) {
            $this->db = $this->container->getPDO($type, $host, $name, $user, $pass, $options, $charset);
        }
    }

    /**
     * Uncaught exception handler.
     */
    public function logException($e) : void
    {
        if ($this->options['debug'] == true) {
            echo $this->container->getErrorController()->debug($e);
        }
        else {
            $errorCode = 500;

            echo $this->container->getErrorController()->handleError($errorCode, '', $e);
        }

        exit;
    }

    /**
     * Checks for a fatal error, work around for set_error_handler not working on fatal errors.
     */
    public function checkFatal() : void
    {
        $error = error_get_last();
        if ($error['type'] == E_ERROR) {
            $this->logError($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }

    /**
     * Error handler, passes flow over the exception logger with new ErrorException.
     */
    public function logError(int $num, string $str, string $file, int $line, $context = null) : void
    {
        $this->logException(new \ErrorException( $str, 0, $num, $file, $line ));
    }
}