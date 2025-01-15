<?php declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Prim\Container;
use org\bovigo\vfs\{vfsStream, vfsStreamDirectory};

use Prim\Console\{Console, Input, Output};
use Prim\Service;
use Tests\Mocks\Command;

class ConsoleTest extends TestCase
{
    /**
     * @var  vfsStreamDirectory
     */
    private $root;

    public function setUp(): void
    {
        $structure = [
            'app' => [
              'config' => [
                  'config.php' => '<?php return [];',
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
            'app' => vfsStream::url('root/app/'),
            'project_name' => 'Tests',
            'root' => vfsStream::url('root/')
        ];

        $service = $this->createMock(Service::class);
        $container = new Container($conf, [], $service);

        $input = new Input(['bin/prim', 'command', '--flag', '--param=value', 'firstArg', 'secondArg']);
        $output = new Output(vfsStream::url('root/stdout'));

        $console = new Console($container, $conf, $input, $output, []);

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

    public function testRunNotExistingCommand()
    {
        $this->expectException(\Exception::class);

        $conf = [
            'db_enable' => false,
            'app' => vfsStream::url('root/app/'),
            'project_name' => 'Tests',
            'root' => __DIR__ . '/'
        ];

        $service = $this->createMock(Service::class);

        $container = new Container($conf, [], $service);

        $input = new Input(['bin/prim', 'nope'], vfsStream::url('root/WrongCommandStdin'));
        $output = new Output(vfsStream::url('root/stdout'));

        $console = new Console($container, $conf, $input, $output);

        $console->addCommand( Command::class);

        $console->run();
    }

    public function testRunWithoutCommand()
    {
        $conf = [
            'db_enable' => false,
            'app' => vfsStream::url('root/app/'),
            'project_name' => 'Tests',
            'root' => vfsStream::url('root/')
        ];

        $service = $this->createMock(Service::class);

        $container = new Container($conf, [], $service);

        $input = new Input(['bin/prim']);
        $output = new Output(vfsStream::url('root/stdout'));

        $console = new Console($container, $conf, $input, $output);

        $console->addCommand(Command::class);

        $console->run();

        $this->assertEquals("test - this is a test command", $output->getLastLine());
    }
}
