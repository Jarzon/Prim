<?php declare(strict_types=1);
namespace Prim;

use ErrorException;

class Application
{
    protected array $options = [];
    protected bool $errorReported = false;

    public function __construct(protected Container $container, array $options = [])
    {
        $this->options = $options += [
            'debug' => false,
            'disableRouter' => false,
            'disableCustomErrorHandler' => false
        ];

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
        set_error_handler( [$this, 'logError'] ); /** @phpstan-ignore-line */
        set_exception_handler( [$this, 'logException'] );
    }

    /**
     * Uncaught exception handler.
     */
    public function logException(\Throwable $e): void
    {
        if($this->errorReported) return;
        $this->errorReported = true;

        if(get_class($e) === 'PDOException' && in_array($e->getCode(), [1045, 2002, 2003, 2006, 2008, 2013, 2014, 2055])) {
            $this->container
                ->get('view')
                ->render('errors/503', 'PrimPack', $this->options['debug'] === true? ['error' => $e->getMessage()] : []);
            exit;
        }

        if ($this->options['debug'] === true) {
            echo $this->container->get('errorController')->debug($e);
        }
        else {
            $errorCode = 500;

            echo $this->container->get('errorController')->handleError($errorCode, [], $e);
        }

        if(get_class($e) === 'ErrorException') {
            throw $e;
        } else {
            exit;
        }
    }

    /**
     * Checks for a fatal error, work around for set_error_handler not working on fatal errors.
     */
    public function checkFatal(): void
    {
        $error = error_get_last();
        if (!empty($error)) {
            $this->logError($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }

    /**
     * Error handler, passes flow over the exception logger with new ErrorException.
     */
    public function logError(int $num, string $str, string $file, int $line): void
    {
        $this->logException(new ErrorException( $str, 0, $num, $file, $line ));
    }

    public function getContainer(): Container
    {
        return $this->container;
    }
}
