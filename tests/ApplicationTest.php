<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Prim\Application;
use Tests\Mocks\Container;

class ApplicationTest extends TestCase
{
    public function testApplicationConstruct()
    {
        $conf = [
            'view.class' => '\Tests\Mocks\View',
            'router.class' => '\\Prim\\Router',
            'pdo.class' => '\Tests\Mocks\PDO',
            'errorController.class' => '\Tests\Mocks\Controller'
        ];

        $app = new Application(new Container($conf, []), $conf);

        return $app;
    }
}