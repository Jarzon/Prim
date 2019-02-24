<?php
namespace Tests\Mocks;

class Command extends \Prim\Console\Command
{
    public $works = false;

    public function __construct($input = null, $output = null)
    {
        parent::__construct($input, $output);

        $this
            ->setName('test')
            ->setDescription('this is a test command');
    }

    public function exec()
    {
        $this->works = true;
    }
}