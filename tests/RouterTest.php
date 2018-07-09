<?php
declare(strict_types=1);

namespace Tests;

use FastRoute\DataGenerator\CharCountBased;
use FastRoute\RouteParser\Std;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

use Prim\Container;
use Prim\Router;

class RouterTest extends TestCase
{
    /**
     * @var  vfsStreamDirectory
     */
    private $root;

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

    public function testRouterConstruct()
    {
        $router = new Router(new \FastRoute\RouteCollector(new Std(), new CharCountBased()), new Container(), [
            'root' => vfsStream::url('root/')
        ]);

        $this->assertEquals(2, $router->getRoutesCount());

        return $router;
    }
}