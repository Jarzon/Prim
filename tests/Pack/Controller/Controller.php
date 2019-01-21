<?php
namespace Tests\Pack\Controller;

class Controller extends \Tests\Mocks\Controller
{
    public $methodCalled = false;

    function aMethod() {
        $this->methodCalled = true;
    }
}