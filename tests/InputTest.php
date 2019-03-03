<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

use Prim\Console\Input;

class InputTest extends TestCase
{
    /**
     * @var  vfsStreamDirectory
     */
    private $root;

    public function setUp()
    {
        $structure = [
            'stdin' => 'command --flag --param=value firstArg secondArg',
        ];

        $this->root = vfsStream::setup('root', null, $structure);
    }

    public function testConstruct()
    {
        $input = new Input(vfsStream::url('root/stdin'));

        $this->assertIsObject($input);

        return $input;
    }

    /**
     * @depends testConstruct
     */
    public function testGetArgument(Input $input)
    {
        $this->assertEquals("firstArg", $input->getArgument(0));
    }

    // test parameters

    // test flags

    // test arguments
}