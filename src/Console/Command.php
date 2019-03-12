<?php
namespace Prim\Console;

class Command
{
    protected $name;
    protected $desc;

    protected $console;
    protected $input;
    protected $output;

    public function __construct($console, $input = null, $output = null)
    {
        $this->console = $console;

        if($input === null) {
            $input = $console->getInput();
        }

        if ($output === null) {
            $output = $console->getOutput();
        }

        $this->input = $input;
        $this->output = $output;
    }

    protected function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    protected function setDescription(string $desc)
    {
        $this->desc = $desc;

        return $this;
    }

    public function getDescription()
    {
        return $this->desc;
    }

    public function getSignature()
    {
        return $this->getName() . ' - ' . $this->getDescription();
    }

    public function exec()
    {
        throw new \Exception("Unimplemented command exec method.");
    }
}