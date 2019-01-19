<?php

namespace Prim;

class Service
{
    /** @var Container */
    protected $container;

    protected $options = [];
    public $services = [];

    public function __construct($container, array $options = [])
    {
        $this->container = $container;

        $this->options = $options += [
            'root' => ''
        ];

        include("{$this->options['root']}app/config/services.php");
    }

    function getServicesInjection($obj)
    {
        $inject = [];

        // TODO: Add glob injection in services

        if(!isset($this->services[$obj])) {
            return false;
        }

        $services = $this->services[$obj]($this);
        foreach ($services as $service) {
            $inject[] = $service;
        }

        return $inject;
    }

    function getServices(string $pack, string $serviceFile = 'services.php'): void
    {
        if($vendorPath = $this->container->getPackList()->getVendorPath($pack)) {
            $vendorFile = "{$this->options['root']}$vendorPath/config/$serviceFile";
        }

        $localFile = "{$this->options['root']}src/$pack/config/$serviceFile";

        foreach ([$vendorFile, $localFile] as $file) {
            if($services = $this->fetchConfigFile($file)) {
                continue;
            }
        }

        if(!$services) throw new \Exception("Can't find services file $serviceFile in $pack");

        $this->addServices($services);
    }

    function addServices(array $services)
    {
        $this->services += $services;
    }

    function fetchConfigFile(string $file): ?array
    {
        if(file_exists($file)) {
            return include($file);
        }
    }
}