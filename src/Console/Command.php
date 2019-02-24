<?php
namespace Prim\Console;

class Command
{
    protected $name;
    protected $desc;

    protected $input;
    protected $output;

    public function __construct($input = null, $output = null)
    {
        if($input === null) {
            $input = new Input();
        }

        if ($output === null) {
            $output = new Output();
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