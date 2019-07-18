<?php declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Prim\Application;
use Tests\Mocks\Container;

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

        $serviceMock = new \Tests\Mocks\Service(null, $conf);

        $container = new Container($conf, [], $serviceMock);

        $app = new Application($container, $conf);

        $this->assertIsObject($app->getContainer());

        return $app;
    }
}
