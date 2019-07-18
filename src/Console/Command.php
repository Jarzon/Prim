<?php declare(strict_types=1);
namespace Prim\Console;

use Exception;

class Command
{
    protected $name;
    protected $desc;

    protected $options;
    protected $input;
    protected $output;

    public function __construct($options, Input $input = null, Output $output = null)
    {
        $this->options = $options;

        if($input === null) {
            $input = new Input($_SERVER['argv']);
        }

        if($output === null) {
            $input = new Output();
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
        throw new Exception("Unimplemented command exec method.");
    }
}
