<?php declare(strict_types=1);
namespace Prim\Console;

class Input
{
    protected string $execSource = '';
    protected ?string $command = '';

    protected array $flags = [];
    protected array $parameters = [];
    protected array $arguments = [];

    protected string $stdin;

    public function __construct($argv, $stdin = null)
    {
        if($stdin === null) {
            $stdin = 'php://stdin';
        }

        $this->stdin = $stdin;

        $this->setCommandArguments($argv);
    }

    public function setCommandArguments(array $args)
    {

        $this->execSource = array_shift($args);
        $this->command = array_shift($args);

        foreach ($args as $arg) {

            if(strpos($arg, '--') === false) {
                $this->arguments[] = $arg;

                continue;
            }

            $arg = str_replace('--', '', $arg);

            if(strpos($arg, '=') !== false) {
                $e = explode('=', $arg);

                $this->parameters[$e[0]] = $e[1];

                continue;
            }

            $this->flags[] = $arg;
        }
    }

    public function getCommand(): ?string
    {
        return $this->command;
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function getArgument(int $number)
    {
        if(!isset($this->arguments[$number])) {
            return null;
        }

        return $this->arguments[$number];
    }

    public function getFlag($name)
    {
        if(in_array($name, $this->flags)) {
            return true;
        }

        return false;
    }

    public function getParameter($name)
    {
        if(!isset($this->parameters[$name])) {
            return null;
        }

        return $this->parameters[$name];
    }

    public function read()
    {
        $stdin = fopen($this->stdin, 'r');

        $input = trim(fgets($stdin));

        fclose($stdin);

        return $input;
    }
}
