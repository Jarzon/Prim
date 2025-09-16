<?php declare(strict_types=1);
namespace Prim\Console;

class Input
{
    protected string $execSource = '';
    protected ?string $command = '';
    /** @var array<mixed> */
    protected array $flags = [];
    /** @var array<mixed> */
    protected array $parameters = [];
    /** @var array<mixed> */
    protected array $arguments = [];

    protected string $stdin;

    /** @param array<mixed> $argv */
    public function __construct(array $argv, string|null $stdin = null)
    {
        if($stdin === null) {
            $stdin = 'php://stdin';
        }

        $this->stdin = $stdin;

        $this->setCommandArguments($argv);
    }

    /** @param array<mixed> $args */
    public function setCommandArguments(array $args): void
    {

        $this->execSource = array_shift($args);
        $this->command = array_shift($args);

        foreach ($args as $arg) {

            if(!str_contains($arg, '--')) {
                $this->arguments[] = $arg;

                continue;
            }

            $arg = str_replace('--', '', $arg);

            if(str_contains($arg, '=')) {
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

    /** @return array<mixed> */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getArgument(int $number): mixed
    {
        if(!isset($this->arguments[$number])) {
            return null;
        }

        return $this->arguments[$number];
    }

    public function getFlag(string $name): bool
    {
        if(in_array($name, $this->flags)) {
            return true;
        }

        return false;
    }

    public function getParameter(string $name): mixed
    {
        if(!isset($this->parameters[$name])) {
            return null;
        }

        return $this->parameters[$name];
    }

    public function read(): string
    {
        $stdin = fopen($this->stdin, 'r');

        $read = fgets($stdin);

        if(!$read) {
            throw new \Exception("Can't read stdin stream: {$this->stdin}");
        }

        $input = trim($read);

        fclose($stdin);

        return $input;
    }
}
