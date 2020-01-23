<?php declare(strict_types=1);
namespace Prim\Console;

use Exception;
use Prim\Container;

class Console
{
    protected string $root = '\\';
    protected array $commands = [];

    protected Container $container;
    protected Input $input;
    protected Output $output;

    public function __construct(Container $container, string $root, Input $input = null, Output $output = null, array $commands = null)
    {
        $this->root = $root;

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

        if($commands === null) include("{$this->root}app/config/commands.php");
    }

    function run()
    {
        // Didnt supply any command then list help
        if($this->input->getCommand() == '') {
            $this->listCommands();

            return;
        }

        $this->getCommand()->exec();
    }

    function addCommand(string $commandName)
    {
        $command = $this->container->getCommand($commandName, $this->input, $this->output);

        $this->commands[$command->name] = $command;
    }

    function getCommand($name = null) {
        if($name === null) {
            $name = $this->input->getCommand();
        }

        if(!isset($this->commands[$name])) {
            throw new Exception("$name command doesn't exist");
        }

        return $this->commands[$name];
    }

    function listCommands()
    {
        foreach ($this->commands as $command) {
            $this->output->writeLine($command->getSignature());
        }
    }

    public  function getInput()
    {
        return $this->input;
    }

    public  function getOutput()
    {
        return $this->output;
    }
}
