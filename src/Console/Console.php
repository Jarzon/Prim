<?php
namespace Prim\Console;

class Console
{
    protected $options = [];
    protected $commands = [];

    protected $input;
    protected $output;

    public function __construct(array $options = [], $input = null, $output = null)
    {
        $this->options = $options += [
            'root' => '/',
            'environment' => 'dev'
        ];

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
        if($this->input->getCommand() == '') {
            $this->listCommands();

            return;
        }

        $this->getCommand()->exec();
    }

    function addCommand(string $command)
    {
        $command = new $command($this->options, $this->input, $this->output);

        $name = $command->getName();

        $this->commands[$name] = $command;
    }

    function getCommand($name = null) {
        if($name === null) {
            $name = $this->input->getCommand();
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

    public  function getInput()
    {
        return $this->input;
    }

    public  function getOutput()
    {
        return $this->output;
    }

    public function getOptions()
    {
        return $this->options;
    }
}