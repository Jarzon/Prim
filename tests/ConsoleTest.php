<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\{vfsStream, vfsStreamDirectory};

use Prim\Console\{Console, Input, Output};
use Tests\Mocks\Command;

class ConsoleTest extends TestCase
{
    /**
     * @var  vfsStreamDirectory
     */
    private $root;

    public function setUp()
    {
        $structure = [
            'app' => [
              'config' => [
                  'commands.php' => ''
              ]
            ],
            'stdout' => '',
            'WrongCommandStdin' => 'prim nope',
        ];

        $this->root = vfsStream::setup('root', null, $structure);
    }

    public function testConstruct()
    {
        $conf = [
            'db_enable' => false,
            'project_name' => 'Tests',
            'root' => vfsStream::url('root/')
        ];

        $input = new Input(['bin/prim', 'command', '--flag', '--param=value', 'firstArg', 'secondArg']);
        $output = new Output(vfsStream::url('root/stdout'));

        $console = new Console($conf, $input, $output);

        $this->assertIsObject($console);

        return $console;
    }

    /**
     * @depends testConstruct
     */
    public function testAddCommand(Console $console)
    {
        $console->addCommand(new Command($console));

        $console->listCommands();

        $this->assertEquals("test - this is a test command", $console->getOutput()->getLastLine());
    }

    /**
     * @expectedException \Exception
     */
    public function testRunNotExistingCommand()
    {
        $conf = [
            'db_enable' => false,
            'project_name' => 'Tests',
            'root' => __DIR__ . '/'
        ];

        $input = new Input(['bin/prim', 'nope'], vfsStream::url('root/WrongCommandStdin'));
        $output = new Output(vfsStream::url('root/stdout'));

        $console = new Console($conf, $input, $output);

        $this->assertIsObject($console);

        $console->addCommand(new Command($console));

        $console->run();
    }

    public function testRunWithoutCommand()
    {
        $conf = [
            'db_enable' => false,
            'project_name' => 'Tests',
            'root' => vfsStream::url('root/')
        ];

        $input = new Input(['bin/prim']);
        $output = new Output(vfsStream::url('root/stdout'));

        $console = new Console($conf, $input, $output);

        $console->addCommand(new Command($console));

        $console->run();

        $this->assertEquals("test - this is a test command", $output->getLastLine());
    }
}