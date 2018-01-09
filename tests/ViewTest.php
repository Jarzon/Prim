<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

use Tests\Mocks\Container;
use Tests\Mocks\View;

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

    public function testConstruct()
    {
        $container = new Container();
        $view = $container->getView();

        $view->root = vfsStream::url('root/');

        var_dump($view);

        $this->assertEquals(true, $view->build);

        return $view;
    }


    /**
    * @depends testConstruct
    */
    public function testBasicRender($view)
    {
        $view->start('default');
        $view->render('test', 'BasePack', [], false);
        $view->end();

        $this->assertEquals('static page', $view->section('default'));
    }
}