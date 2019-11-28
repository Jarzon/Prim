<?php declare(strict_types=1);

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

    public function setUp(): void
    {
        $structure = [
            'stdin' => 'input',
        ];

        $this->root = vfsStream::setup('root', null, $structure);
    }

    public function testConstruct()
    {
        $input = new Input(['bin/prim', 'command', '--flag', '--param=value', 'firstArg', 'secondArg'], vfsStream::url('root/stdin'));

        $this->assertIsObject($input);

        return $input;
    }

    /**
     * @depends testConstruct
     */
    public function testGetUnsetArgument(Input $input)
    {
        $this->assertEquals(null, $input->getArgument(2));
    }

    /**
     * @depends testConstruct
     */
    public function testGetArgument(Input $input)
    {
        $this->assertEquals("firstArg", $input->getArgument(0));
    }

    /**
     * @depends testConstruct
     */
    public function testGetUnsetParameter(Input $input)
    {
        $this->assertEquals(null, $input->getParameter('noparam'));
    }

    /**
     * @depends testConstruct
     */
    public function testGetParameter(Input $input)
    {
        $this->assertEquals('value', $input->getParameter('param'));
    }

    /**
     * @depends testConstruct
     */
    public function testGetUnsetFlag(Input $input)
    {
        $this->assertEquals(false, $input->getFlag('noflag'));
    }

    /**
     * @depends testConstruct
     */
    public function testGetFlag(Input $input)
    {
        $this->assertEquals(true, $input->getFlag('flag'), 'Input->getFlag should return true whem flag exist.');
    }
}
