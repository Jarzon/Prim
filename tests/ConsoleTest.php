<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Prim\Console\Console;
use Tests\Mocks\Command;
use Tests\Mocks\Container;

class ConsoleTest extends TestCase
{
    public function testConstruct()
    {
        $conf = [
            'db_enable' => false,
            'project_name' => 'Tests',
            'root' => __DIR__ . '/'
        ];

        $container = new Container([], $conf);

        $argv = ['cmd', 'what'];

        $console = new Console($container, $conf);

        $this->assertIsObject($console->container);

        return $console;
    }

    /**
     * @depends testConstruct
     */
    public function testAddCommand(Console $console)
    {
        $console->addCommand(new Command());

        ob_start();
            $console->listCommands();
        $output = ob_get_clean();

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

        $argv = ['prim', 'unsetCommand'];

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

        $argv = ['prim', 'test', 'what'];

        $console = new Console($container, $conf);

        $this->assertIsObject($console->container);

        $command = new Command();

        $console->addCommand($command);

        $console->run();

        $this->assertEquals(true, $command->works);
    }

    // test parameters

    // test flags

    // test arguments
}