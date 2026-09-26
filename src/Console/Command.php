<?php declare(strict_types=1);
namespace Prim\Console;

use Exception;

class Command
{
    public string $name;
    public string $description;
    /** @var array<mixed> */

    protected array $options = [];
    protected Input $input;
    protected Output $output;

    /** @param array<mixed> $options */
    public function __construct(array $options, Input|null $input = null, Output|null $output = null)
    {
        $this->options = $options;

        if($input === null) {
            $input = new Input($_SERVER['argv']);
        }

        if($output === null) {
            $output = new Output();
        }

        $this->input = $input;
        $this->output = $output;
    }

    protected function setName(string $name): Command
    {
        $this->name = $name;

        return $this;
    }

    protected function setDescription(string $desc): Command
    {
        $this->description = $desc;

        return $this;
    }

    public function getSignature(): string
    {
        return $this->name . ' - ' . $this->description;
    }

    public function exec(): void
    {
        throw new Exception("Unimplemented command exec method.");
    }
}
