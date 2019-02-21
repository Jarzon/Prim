<?php
namespace Prim;

class Console extends Application
{
    protected $options = [];
    protected $commands = [];

    protected $execSource = '';
    protected $command = '';
    protected $flags = [];
    protected $parameters = [];
    protected $arguments = [];

    public function __construct($container, array $options = [], $argv = [])
    {
        $options = [
            'disableRouter' => true,
            'disableCustomErrorHandler' => true
        ] + $options;

        parent::__construct($container, $options);

        include("{$this->options['root']}app/config/commands.php");

        $this->setCommandArguments($argv);
    }

    function run()
    {
        // Didnt supply any command then list help
        if($this->command === null) {
            $this->listCommands();

            return;
        }

        $this->getCommand()->exec($this->arguments);
    }

    function setCommandArguments($argv)
    {
        $this->execSource = array_shift($argv);

        $this->command = array_shift($argv);

        foreach ($argv as $arg) {
            // flag
            if(strpos($arg, '--') !== false) {
                $this->flags[] = $arg;
            }
            // option
            else if(strpos($arg, '-') !== false) {
                $this->parameters[] = $arg;
            } else {
                $this->arguments[] = $arg;
            }
        }
    }

    function addCommand($command)
    {
        $name = $command->getName();

        $this->commands[$name] = $command;
    }

    function getCommand($name = null) {
        if($name === null) {
            $name = $this->command;
        }

        if(!isset($this->commands[$name])) {
            throw new \Exception("$name command doesn't exist");
        }

        return $this->commands[$name];
    }

    function listCommands()
    {
        $output = '';

        foreach ($this->commands as $command) {
            $output .= "{$command->getSignature()}\r\n";
        }

        return $output;
    }
}