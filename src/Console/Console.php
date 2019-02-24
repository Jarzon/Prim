<?php
namespace Prim\Console;

use Prim\Application;

class Console extends Application
{
    protected $options = [];
    protected $commands = [];

    protected $input;
    protected $output;

    public function __construct($container, array $options = [], $input = null, $output = null)
    {
        $options = [
            'disableRouter' => true,
            'disableCustomErrorHandler' => true
        ] + $options;

        parent::__construct($container, $options);

        if($input === null) {
            $input = new Input();
        }

        if($output === null) {
            $output = new Output();
        }

        $this->input = $input;
        $this->output = $output;

        include("{$this->options['root']}app/config/commands.php");
    }

    function run()
    {
        // Didnt supply any command then list help
        if($this->command === '') {
            $this->listCommands();

            return;
        }

        $this->getCommand()->exec($this->arguments);
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
        foreach ($this->commands as $command) {
            $this->output->writeLine($command->getSignature());
        }
    }
}