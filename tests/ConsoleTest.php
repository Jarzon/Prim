<?php declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\{vfsStream, vfsStreamDirectory};

use Prim\Console\{Console, Input, Output};
use Tests\Mocks\Command;
use Tests\Mocks\Container;

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

        $serviceMock = new \Tests\Mocks\Service(null, $conf);

        $container = new Container($conf, [], $serviceMock);

        $input = new Input(['bin/prim', 'command', '--flag', '--param=value', 'firstArg', 'secondArg']);
        $output = new Output(vfsStream::url('root/stdout'));

        $console = new Console($container, $conf['root'], $input, $output);

        $this->assertIsObject($console);

        return $console;
    }

    /**
     * @depends testConstruct
     */
    public function testAddCommand(Console $console)
    {
        $console->addCommand( Command::class);

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

        $serviceMock = new \Tests\Mocks\Service(null, $conf);

        $container = new Container($conf, [], $serviceMock);

        $input = new Input(['bin/prim', 'nope'], vfsStream::url('root/WrongCommandStdin'));
        $output = new Output(vfsStream::url('root/stdout'));

        $console = new Console($container, $conf['root'], $input, $output);

        $this->assertIsObject($console);

        $console->addCommand( Command::class);

        $console->run();
    }

    public function testRunWithoutCommand()
    {
        $conf = [
            'db_enable' => false,
            'project_name' => 'Tests',
            'root' => vfsStream::url('root/')
        ];

        $serviceMock = new \Tests\Mocks\Service(null, $conf);

        $container = new Container($conf, [], $serviceMock);

        $input = new Input(['bin/prim']);
        $output = new Output(vfsStream::url('root/stdout'));

        $console = new Console($container, $conf['root'], $input, $output);

        $console->addCommand(Command::class);

        $console->run();

        $this->assertEquals("test - this is a test command", $output->getLastLine());
    }
}
