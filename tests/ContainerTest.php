<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tests\Mocks\Container;

class ContainerTest extends TestCase
{
    public function testOpenDatabaseConnection()
    {
        $conf = [
            'db_enable' => true,
            'disable_services_injection' => true
        ];

        $container = new Container(['pdo.class' => '\Tests\Mocks\PDO', 'service.class' => '\Tests\Mocks\Service'], $conf);

        $this->assertIsObject($container->getPDO());
    }
}