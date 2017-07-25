<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ViewMock extends Prim\View {
    public $build = false;

    function buildThis() {
        $this->build = true;
    }
}

class ViewTest extends TestCase
{
    public function testConstruct()
    {
        $view = new ViewMock('');

        $this->assertEquals(true, $view->build);

        return $view;
    }
}