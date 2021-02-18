<?php declare(strict_types=1);

namespace Prim;

class Service
{
    protected Container $container;
    protected PackList $packList;

    protected array $options = [];
    protected array $services = [];

    public function __construct(Container $container, array $options = [], PackList $packList = null, array $services = null)
    {
        $this->container = $container;

        $this->options = $options += [
            'root' => ''
        ];

        $this->packList = $packList ?: new PackList($this->options['root']);

        if($services !== null) {
            $this->services = $services;
        } else {
            $this->loadServices();
        }
    }

    function loadServices() {
        include("{$this->options['root']}app/config/services.php");
    }

    function getServicesInjection(string|object $obj): array
    {
        $injections = [];

        if(str_contains($obj, '\\')) {
            $namespaces = explode('\\', $obj);

            foreach ($namespaces as &$namespace) {
                if($namespace !== $this->options['project_name'] && $namespace !== '') {
                    $namespace = "($namespace|\*)";
                }
            }

            $namespaces = implode('\\\\', $namespaces);

            $injections = array_merge($injections, $this->preg_grep_keys("/^{$namespaces}$/", $this->services));
        }
        else if(isset($this->services[$obj])) {
            // Exact match
            $injections[] = $this->services[$obj];
        }

        $inject = [];

        foreach ($injections as $callback) {
            $services = $callback($this->container);

            $inject = array_merge($inject, $services);
        }

        return $inject;
    }

    protected function preg_grep_keys($pattern, $input, $flags = 0): array
    {
        return array_intersect_key($input, array_flip(preg_grep($pattern, array_keys($input), $flags)));
    }

    function registerServices(string $pack, string $serviceFile = 'services.php'): Service
    {
        if($vendorFile = $this->packList->getVendorPath($pack)) {
            $vendorFile = "{$this->options['root']}$vendorFile/config/$serviceFile";
        }

        $localFile = "{$this->options['root']}src/$pack/config/$serviceFile";
        $services = [];

        foreach ([$vendorFile, $localFile] as $file) {
            if($newServices = $this->fetchConfigFile($file)) {
                $services += $newServices;
            }
        }

        if(!$services) {
            throw new \Exception("Can't find services files $serviceFile for $pack");
        }

        $this->addServices($services);

        return $this;
    }

    function addServices(array $services): void
    {
        $this->services += $services;
    }

    function fetchConfigFile(string $file): array|false
    {
        if(file_exists($file)) {
            return include($file);
        }

        return false;
    }

    function getPackList(): PackList
    {
        return $this->packList;
    }
}
