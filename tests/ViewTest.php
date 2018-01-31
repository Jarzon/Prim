<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

use Prim\Container;

class ViewTest extends TestCase
{
    /**
     * @var  vfsStreamDirectory
     */
    private $root;

    public function setUp()
    {
        $view = <<<'EOD'
static page
EOD;

        $structure = [
            'src' => [
                'BasePack' => [
                    'view' => [
                        'test.php' => $view,
                    ],
                ]
            ]
        ];

        $this->root = vfsStream::setup('root', null, $structure);
    }

    public function testViewConstruct()
    {
        $container = new Container([
            'view.class' => '\Tests\Mocks\View',
            'pdo.class' => '\Tests\Mocks\PDO'
        ], [
            'root' => vfsStream::url('root').'/'
        ]);
        $view = $container->getView();

        $this->assertEquals(true, $view->build);

        return $view;
    }

    /**
     * @depends testViewConstruct
     */
    public function testBasicRender($view)
    {
        $view->start('default');
        $view->render('test', 'BasePack', [], false);
        $view->end();

        $this->assertEquals('static page', $view->section('default'));
    }

    /**
     * @depends testViewConstruct
     * @expectedException \Exception
     */
    public function testMissingView($view)
    {
        $view->render('aViewThatDontExist', 'BasePack', [], false);
    }
}