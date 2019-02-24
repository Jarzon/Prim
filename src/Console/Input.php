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
            if(strpos($arg, '--') !== false) {
                $arg = str_replace('--', '', $arg);

                $e = explode("=", $arg);
                if(count($e) == 2) {
                    $this->parameters[$e[0]] = $e[1];
                } else {
                    $this->flags[] = $arg;
                }
            }
            else {
                $this->arguments[] = $arg;
            }
        }
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function getArgument(int $number)
    {
        return $this->arguments[$number];
    }

    public function getFlag($name)
    {
        if(isset($this->flags[$name])) {
            return true;
        }

        return false;
    }

    public function getParameter($name)
    {
        if(!isset($this->flags[$name])) {
            return false;
        }

        return $this->flags[$name];
    }
}