<?php
namespace Tests\Mocks;

class View extends \Prim\View implements \Prim\ViewInterface
{
    public $build = false;

    function buildThis() {
        $this->build = true;
    }
}