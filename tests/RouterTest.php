<?php declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

use Tests\Mocks\Container;
use Tests\Mocks\Router;

class RouterTest extends TestCase
{
    /**
     * @var  vfsStreamDirectory
     */
    private $root;

    public function setUp()
    {
        $routes = <<<'EOD'
    <?php $this->both('/{id:\d+}', 'Pack\Controller', 'aMethod');
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

    public function testRouterConstruct()
    {
        $conf = [
            'root' => vfsStream::url('root/'),
            'environment' => 'dev',
            'project_name' => 'Tests',
            'router_query_string' => false,
            'server' => [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/1'
            ]
        ];

        $container = $this->createMock(Container::class);

        $router = new Router($container, $conf);

        $this->assertEquals(2, $router->getRoutesCount());

        return $router;
    }

    /**
     * @depends testRouterConstruct
     */
    public function testDispatchRoute(Router $router)
    {
        $controller = $router->dispatchRoute();

        $this->assertEquals(true, $controller->methodCalled);

        return $router;
    }
}
