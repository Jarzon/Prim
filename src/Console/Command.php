<?php declare(strict_types=1);
namespace Prim\Console;

use Exception;

class Command
{
    public string $name;
    public string $description;

    protected array $options = [];
    protected Input $input;
    protected Output $output;

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

    protected function setDescription(string $desc)
    {
        $this->description = $desc;

        return $this;
    }

    public function getSignature()
    {
        return $this->name . ' - ' . $this->description;
    }

    public function exec()
    {
        throw new Exception("Unimplemented command exec method.");
    }
}
