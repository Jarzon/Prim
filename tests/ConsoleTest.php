<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\{vfsStream, vfsStreamDirectory};

use Prim\Console\{Console, Input, Output};
use Tests\Mocks\{Command, Container};

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
            'stdin' => 'command --flag --param=value firstArg secondArg',
            'stdout' => '',
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

        $input = new Input(vfsStream::url('root/stdin'));
        $output = new Output(vfsStream::url('root/stdout'));

        $container = new Container([], $conf);

        $console = new Console($container, $conf, $input, $output);

        $this->assertIsObject($console->container);

        return [$console, $input, $output];
    }

    /**
     * @depends testConstruct
     */
    public function testAddCommand(array $objects)
    {
        list($console, $input, $output) = $objects;

        $console->addCommand(new Command());

        $console->listCommands();

        $this->assertEquals("test - this is a test command\n", $output);
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

        $container = new Container([], $conf);

        $console = new Console($container, $conf);

        $this->assertIsObject($console->container);

        $console->addCommand(new Command());

        $console->run();

        $this->assertEquals("test\r\n", $console->listCommands());
    }

    public function testRun()
    {
        $conf = [
            'db_enable' => false,
            'project_name' => 'Tests',
            'root' => __DIR__ . '/'
        ];

        $container = new Container([], $conf);

        $console = new Console($container, $conf);

        $this->assertIsObject($console->container);

        $command = new Command();

        $console->addCommand($command);

        $console->run();

        $this->assertEquals(true, $command->works);
    }
}