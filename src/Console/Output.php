<?php
namespace Prim\Console;

class Output
{
    public $stdout;

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

        fputs($stdout, "$output\n");

        fclose($stdout);
    }
}