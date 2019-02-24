<?php
namespace Prim\Console;

class Input
{
    protected $execSource = '';
    protected $command = '';

    protected $flags = [];
    protected $parameters = [];
    protected $arguments = [];

    public function __construct()
    {
        global $argv;

        $this->setCommandArguments($argv);
    }

    public function setCommandArguments($argv)
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
}