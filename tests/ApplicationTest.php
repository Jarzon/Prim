<?php declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Prim\Application;
use Prim\Container;

class ApplicationTest extends TestCase
{

    public function testApplicationConstruct()
    {
        $conf = [
            'project_name' => 'test',
            'disableCustomErrorHandler' => true,
            'disableRouter' => true,
            'db_enable' => false
        ];

        $container = $this->createMock(Container::class);

        $app = new Application($container, $conf);

        $this->assertIsObject($app->getContainer());

        return $app;
    }
}
