<?php declare(strict_types=1);
namespace Prim\Console;

class Output
{
    protected $stdout;
    protected $lastLine = '';

    public function __construct($stdout = null)
    {
        if($stdout === null) {
            $stdout = 'php://stdout';
        }

        $this->stdout = $stdout;
    }

    public function writeLine(string $output)
    {
        $stdout = fopen($this->stdout, 'w');

        $this->lastLine = $output;

        fputs($stdout, $output . PHP_EOL);

        fclose($stdout);
    }

    public function getLastLine()
    {
        return $this->lastLine;
    }
}
