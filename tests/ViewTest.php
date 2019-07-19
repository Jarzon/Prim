<?php declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Prim\PackList;
use Prim\View;
use Tests\Mocks\Composer;

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

        $packList = new PackList(vfsStream::url('root'), new Composer());

        $view = new View($packList, $conf);

        $this->assertEquals(1, $this->count($view->vars()));

        return $view;
    }

    /**
     * @depends testViewConstruct
     */
    public function testBasicRender(View $view)
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
    public function testMissingView(View $view)
    {
        $view->render('aViewThatDontExist', 'BasePack', [], false);
    }
}
