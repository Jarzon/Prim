<?php declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

use Prim\Console\Output;

class OutputTest extends TestCase
{
    /**
     * @var  vfsStreamDirectory
     */
    private $root;

    public function setUp(): void
    {
        $structure = [
            'stdout' => '',
        ];

        $this->root = vfsStream::setup('root', null, $structure);
    }

    public function testConstruct()
    {
        $stdout = vfsStream::url('root/stdout');

        $output = new Output($stdout);

        $this->assertIsObject($output);

        return $output;
    }

    /**
     * @depends testConstruct
     */
    public function testWriteLine($output)
    {
        $output->writeLine('test');

        $stdout = fopen(vfsStream::url('root/stdout'), 'r');

        $this->assertEquals("test\n", fgets($stdout));

        fclose($stdout);
    }
}
