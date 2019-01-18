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
            'debug' => false,
            'environment' => 'dev',

            'db_enable' => false,
            'db_type' => 'mysql',
            'db_name' => $options['project_name']?? '',
            'db_host' => '127.0.0.1',
            'db_user' => 'root',
            'db_password' => '',
            'db_charset' => 'utf8',
            'db_options' => [],

            'disableRouter' => false,
            'disableCustomErrorHandler' => false
        ];

        $this->container = $container;

        if(!$this->options['disableCustomErrorHandler']) {
            $this->setErrorHandlers();
        }

        $this->openDatabaseConnection($options);

        if(!$this->options['disableRouter']) {
            $router = $container->getRouter();
            $router->dispatchRoute();
        }
    }

    public function setErrorHandlers() : void
    {
        register_shutdown_function( [$this, 'checkFatal'] );
        set_error_handler( [$this, 'logError'] );
        set_exception_handler( [$this, 'logException'] );
    }

    public function openDatabaseConnection(array $options) : void
    {
        if($options['db_enable']) {
            $this->db = $this->container->getPDO($options['db_type'], $options['db_host'], $options['db_name'], $options['db_user'], $options['db_password'], $options['db_options'], $options['db_charset']);
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