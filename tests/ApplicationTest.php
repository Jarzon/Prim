<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Prim\Application;
use Tests\Mocks\Container;

use org\bovigo\vfs\vfsStream;

class ApplicationTest extends TestCase
{
    public function setUp()
    {
        $routes = <<<'EOD'
    <?php $this->both('/', 'aController', 'aMethod');
EOD;

        $structure = [
            'app' => [
                'config' => [
                    'routing.php' => $routes,
                ]
            ]
        ];

        $this->root = vfsStream::setup('root', null, $structure);
    }

    public function testApplicationConstruct()
    {
        $conf = [
            'disableCustomErrorHandler' => true,
            'disableRouter' => true,
            'db_enable' => false
        ];

        $container = new Container($conf, ['root' => 'vfs://root/']);

        $app = new Application($container, $conf);

        $this->assertIsObject($app->container);

        return $app;
    }
}