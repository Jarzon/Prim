<?php
namespace Prim;

use \PDO;

class Application
{
    /**
     * @var Container $container
     * @var Controller $router
     */
    public $container;
    public $router;
    public $projectNamespace;

    public function __construct(Container $container)
    {
        $this->container = $container;

        $this->projectNamespace = PROJECT_NAME;

        if(DB_ENABLE) {
            $this->openDatabaseConnection(DB_TYPE, DB_HOST, DB_NAME, DB_CHARSET, DB_USER, DB_PASS);
        }

        $this->setErrorHandlers();

        $this->definePaths();

        $dispatcher = \FastRoute\cachedDispatcher(function(\FastRoute\RouteCollector $router) {
            $this->router = $this->container->getRouter($router);
        }, [
            'cacheFile' => ROOT . '/app/cache/route.cache',
            'cacheDisabled' => (ENV === 'dev'),
        ]);

        $routeInfo = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], URL_RELATIVE_BASE);

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

                if(class_exists("$this->projectNamespace\\$controllerNamespace")) {
                    $controllerNamespace = "$this->projectNamespace\\$controllerNamespace";
                } else if(!class_exists($controllerNamespace)) {
                    throw new \Exception("Can't find controller: $controllerNamespace");
                }

                $controller = $container->getController($controllerNamespace);
                $method = $handler[1];

                $controller->$method(...$vars);
                break;
        }
    }

    public function setErrorHandlers() {
        register_shutdown_function( [$this, 'checkFatal'] );
        set_error_handler( [$this, 'logError'] );
        set_exception_handler( [$this, 'logException'] );
    }

    public function definePaths() {
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
    }

    /**
     * Open a Database connection using PDO
     */
    public function openDatabaseConnection(string $type, string $host, string $name, string $charset, string $user, string $pass)
    {
        // Set the fetch mode to object
        $options = [
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_TO_STRING
        ];

        // generate a database connection, using the PDO connector
        try {
            $this->db = $this->container->getPDO($type, $host, $name, $charset, $user, $pass, $options);
        } catch (\PDOException $e) {
            header('HTTP/1.1 503 Service Unavailable');
            throw new \Exception('Database connection could not be established.');
        }
    }

    /**
     * Uncaught exception handler.
     */
    public function logException($e)
    {
        if (DEBUG == true ) {
            echo $this->container->getErrorController()->debug($e);
        }
        else {
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'To: ' . ERROR_MAIL;
            $headers[] = 'From: ' . ERROR_MAIL_FROM;

            $message[] = 'Type: ' . get_class($e);
            $message[] = "Message: {$e->getMessage()}";
            $message[] = "File: {$e->getFile()}";
            $message[] = "Line: {$e->getLine()}";
            $message = wordwrap(implode("\r\n", $message), 70, "\r\n");

            mail(ERROR_MAIL, 'PHP Error', $message, implode("\r\n", $headers));

            echo $this->container->getErrorController()->handleError(500);
            exit;
        }
    }

    /**
     * Checks for a fatal error, work around for set_error_handler not working on fatal errors.
     */
    public function checkFatal()
    {
        $error = error_get_last();
        if ($error['type'] == E_ERROR) {
            $this->logError($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }

    /**
     * Error handler, passes flow over the exception logger with new ErrorException.
     */
    public function logError($num, $str, $file, $line, $context = null)
    {
        $this->logException(new \ErrorException( $str, 0, $num, $file, $line ));
    }
}