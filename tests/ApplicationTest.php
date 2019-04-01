<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Prim\Application;
use Tests\Mocks\Container;

class ApplicationTest extends TestCase
{

    public function testApplicationConstruct()
    {
        $conf = [
            'disableCustomErrorHandler' => true,
            'disableRouter' => true,
            'db_enable' => false
        ];

        $container = new Container([], $conf);

        $app = new Application($container, $conf);

        $this->assertIsObject($app->getContainer());

        return $app;
    }
}