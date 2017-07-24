<?php

class ViewMock extends Prim\View {
    public $build = false;

    function buildThis() {
        $this->build = true;
    }
}

class ViewTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $view = new ViewMock('');

        $this->assertEquals(true, $view->build);

        return $view;
    }
}