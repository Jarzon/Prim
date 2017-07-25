<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    public function testConstruct()
    {
        define('ROOT', '');

        $view = new \Tests\Mocks\View('');

        $this->assertEquals(true, $view->build);

        return $view;
    }
}