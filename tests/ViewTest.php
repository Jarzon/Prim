<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Prim\View;
use Tests\Mocks\Container;

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
        $conf = [
            'project_name' => 'test',
            'root' => vfsStream::url('root').'/'
        ];

        $serviceMock = new \Tests\Mocks\Service(null, $conf);

        $view = new View(new Container([], [], $serviceMock), $conf);

        $this->assertEquals(1, $this->count($view->vars()));

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