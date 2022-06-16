<?php declare(strict_types=1);
namespace Prim\Console;

class Output
{
    protected string $stdout;
    protected string $lastLine = '';

    public function __construct(string|null $stdout = null)
    {
        if($stdout === null) {
            $stdout = 'php://stdout';
        }

        $this->stdout = $stdout;
    }

    public function writeLine(string $output): void
    {
        $stdout = fopen($this->stdout, 'w');

        $this->lastLine = $output;

        fputs($stdout, $output . PHP_EOL);

        fclose($stdout);
    }

    public function getLastLine(): string
    {
        return $this->lastLine;
    }
}
