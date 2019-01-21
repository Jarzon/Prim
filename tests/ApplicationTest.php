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

        $this->assertIsObject($app->container);

        return $app;
    }

    public function testOpenDatabaseConnection()
    {
        $conf = [
            'disableCustomErrorHandler' => true,
            'disableRouter' => true,
            'db_enable' => true
        ];

        $container = new Container(['pdo.class' => '\Tests\Mocks\PDO', 'service.class' => '\Tests\Mocks\Service'], $conf);

        $app = new Application($container, $conf);

        $this->assertIsObject($app->db);
    }
}