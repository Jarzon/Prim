<?php
namespace Tests\Mocks;

class Command extends \Prim\Command
{
    public $works = false;

    public function __construct()
    {
        $this
            ->setName('test')
            ->setDescription('this is a test command');
    }

    public function exec()
    {
        $this->works = true;
    }
}