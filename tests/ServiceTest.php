<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

use Prim\Container;
use Prim\Service;

class ServiceTest extends TestCase
{
    /**
     * @var  vfsStreamDirectory
     */
    private $root;

    public function setUp()
    {
        $routes = <<<'EOD'
    <?php $this->addServices(['aClass' => (object)[]]);
EOD;

        $structure = [
            'app' => [
                'config' => [
                    'services.php' => $routes,
                ]
            ]
        ];

        $this->root = vfsStream::setup('root', null, $structure);
    }

    public function testConstructor()
    {
        $conf = [
            'root' => vfsStream::url('root/'),
            'environment' => 'dev',
            'router_query_string' => false,
            'server' => [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/'
            ]
        ];

        $service = new Service(new Container(['errorController.class' => '\Tests\Mocks\Controller'], $conf), $conf);

        $this->assertEquals(['aClass' => (object)[]], $service->services);

        return $service;
    }
}