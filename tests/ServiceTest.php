<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

use Prim\Container;
use Prim\Service;

class ServiceTest extends TestCase
{

    public function testConstructor()
    {
        $conf = ['project_name' => 'Project'];

        $container = new Container([], []);

        $service = new Service($container, $container->getPackList(), $conf);

        $service->addServices([
            'aClass' => function() {
                return [];
            },
            '\Project\*\Controller\*' => function() {
                return ['globInjection'];
            },
            '\Project\aPack\Controller\ControllerClass' => function() {
                return ['directInjection'];
            },
            '\aPack\Controller\ControllerClass' => function() {
                return ['subPackInjection'];
            },
        ]);

        $this->assertEquals([], $service->getServicesInjection('aClass'));

        return $service;
    }

    /**
     * @depends testConstructor
     */
    public function testGetServicesInjection($service)
    {
        $this->assertEquals(['globInjection', 'directInjection'], $service->getServicesInjection('\Project\aPack\Controller\ControllerClass'));

        $this->assertEquals(['subPackInjection'], $service->getServicesInjection('\aPack\Controller\ControllerClass'));

        return $service;
    }

    /**
     * @depends testConstructor
     */
    public function testGetServicesVendorPack($service)
    {
        $this->assertEquals([], $service->getServicesInjection('aPack\Controller\ControllerClass'));

        return $service;
    }
}