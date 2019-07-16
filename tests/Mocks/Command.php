<?php
namespace Tests\Mocks;

class Command extends \Prim\Console\Command
{
    public $works = false;

    public function __construct($console, $input = null, $output = null)
    {
        parent::__construct($console, $input, $output);

        $this
            ->setName('test')
            ->setDescription('this is a test command');
    }

    public function exec()
    {
        $this->works = true;
    }
}
