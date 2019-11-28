<?php declare(strict_types=1);
namespace Prim;

use ErrorException;

class Application
{
    protected Container $container;
    protected array $options = [];

    public function __construct(Container $container, array $options = [])
    {
        $this->options = $options += [
            'debug' => false,
            'disableRouter' => false,
            'disableCustomErrorHandler' => false
        ];

        $this->container = $container;

        if(!$this->options['disableCustomErrorHandler']) {
            $this->setErrorHandlers();
        }

        if(!$this->options['disableRouter']) {
            $router = $container->get('router');
            $router->dispatchRoute();
        }
    }

    public function setErrorHandlers(): void
    {
        register_shutdown_function( [$this, 'checkFatal'] );
        set_error_handler( [$this, 'logError'] );
        set_exception_handler( [$this, 'logException'] );
    }

    /**
     * Uncaught exception handler.
     */
    public function logException($e): void
    {
        if ($this->options['debug'] == true) {
            echo $this->container->get('errorController')->debug($e);
        }
        else {
            $errorCode = 500;

            echo $this->container->get('errorController')->handleError($errorCode, '', $e);
        }

        exit;
    }

    /**
     * Checks for a fatal error, work around for set_error_handler not working on fatal errors.
     */
    public function checkFatal(): void
    {
        $error = error_get_last();
        if (!empty($error) && $error['type'] == E_ERROR) {
            $this->logError($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }

    /**
     * Error handler, passes flow over the exception logger with new ErrorException.
     */
    public function logError(int $num, string $str, string $file, int $line, $context = null): void
    {
        $this->logException(new ErrorException( $str, 0, $num, $file, $line ));
    }

    public function getContainer()
    {
        return $this->container;
    }
}
