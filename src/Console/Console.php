<?php declare(strict_types=1);
namespace Prim\Console;

use Exception;
use Hoa\Stream\IStream\Out;
use Prim\Container;

class Console
{
    protected array $options = ['root' => '\\'];
    protected array $commands = [];

    protected Container $container;
    protected Input $input;
    protected Output $output;

    protected bool $errorReported = false;

    public function __construct(Container $container, array $options, Input|null $input = null, Output|null $output = null, array|null $commands = null)
    {
        $this->options = $options;

        if($input === null) {
            global $argv;
            $input = new Input($argv);
        }

        if($output === null) {
            $output = new Output();
        }

        $this->container = $container;
        $this->input = $input;
        $this->output = $output;

        $this->setErrorHandlers();

        if($commands === null) include("{$this->options['root']}app/config/commands.php");
    }

    public function setErrorHandlers(): void
    {
        if ($this->options['debug'] === false) {
            register_shutdown_function([$this, 'checkFatal']);
            set_error_handler([$this, 'logError']);
            set_exception_handler([$this, 'logException']);
        }
    }
    /**
     * Uncaught exception handler.
     */
    public function logException(\Throwable $e): void
    {
        if($this->errorReported) return;
        $this->errorReported = true;

        $this->container->get('errorController')->logError($e);

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
     * Used with set_error_handler to convert some errors to exceptions.
     */
    public function logError(int $num, string $str, string $file, int $line): bool
    {
        throw new \ErrorException( $str, 0, $num, $file, $line );
    }

    function run(): void
    {
        // Didnt supply any command then list help
        if($this->input->getCommand() == '') {
            $this->listCommands();

            return;
        }

        $this->getCommand()->exec();
    }

    function addCommand(string $commandName): void
    {
        $command = $this->container->getCommand($commandName, $this->input, $this->output);

        $this->commands[$command->name] = $command;
    }

    function getCommand(string|null $name = null): mixed
    {
        if($name === null) {
            $name = $this->input->getCommand();
        }

        if(!isset($this->commands[$name])) {
            throw new Exception("$name command doesn't exist");
        }

        return $this->commands[$name];
    }

    function listCommands(): void
    {
        foreach ($this->commands as $command) {
            $this->output->writeLine($command->getSignature());
        }
    }

    public  function getInput(): Input
    {
        return $this->input;
    }

    public  function getOutput(): Output
    {
        return $this->output;
    }
}
