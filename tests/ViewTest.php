<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tests\Mocks\View;

class ViewTest extends TestCase
{
    public function testConstruct()
    {
        $view = new View();

        $this->assertEquals(true, $view->build);

        return $view;
    }

    // TODO: Write tests using VFS to test Views
}