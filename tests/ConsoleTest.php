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
            'stdin' => 'prim test --flag --param=value firstArg secondArg',
            'stdout' => '',
            'WrongCommandStdin' => 'prim nope',
            'EmptyStdin' => 'prim',
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

        $console = new Console($conf, $input, $output);

        $this->assertIsObject($console);

        return [$console, $input, $output];
    }

    /**
     * @depends testConstruct
     */
    public function testAddCommand(array $objects)
    {
        list($console, $input, $output) = $objects;

        $console->addCommand(new Command($input, $output));

        $console->listCommands();

        $this->assertEquals("test - this is a test command", $output->getLastLine());
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

        $input = new Input(vfsStream::url('root/WrongCommandStdin'));
        $output = new Output(vfsStream::url('root/stdout'));

        $console = new Console($conf, $input, $output);

        $this->assertIsObject($console);

        $console->addCommand(new Command($input, $output));

        $console->run();
    }

    public function testRunWithoutCommand()
    {
        $conf = [
            'db_enable' => false,
            'project_name' => 'Tests',
            'root' => vfsStream::url('root/')
        ];

        $input = new Input(vfsStream::url('root/EmptyStdin'));
        $output = new Output(vfsStream::url('root/stdout'));

        $console = new Console($conf, $input, $output);

        $console->addCommand(new Command($input, $output));

        $console->run();

        $this->assertEquals("test - this is a test command", $output->getLastLine());
    }
}