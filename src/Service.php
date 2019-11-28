<?php declare(strict_types=1);

namespace Prim;

class Service
{
    /** @var Container */
    protected $container;
    /** @var PackList */
    protected $packList;

    protected $options = [];
    protected $services = [];

    public function __construct($container, array $options = [], $packList = null, $services = null)
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

    function getServicesInjection($obj)
    {
        $injections = [];

        if(strpos($obj, '\\') !== false) {
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

    protected function preg_grep_keys($pattern, $input, $flags = 0) {
        return array_intersect_key($input, array_flip(preg_grep($pattern, array_keys($input), $flags)));
    }

    function registerServices(string $pack, string $serviceFile = 'services.php')
    {
        if($vendorFile = $this->packList->getVendorPath($pack)) {
            $vendorFile = "{$this->options['root']}$vendorFile/config/$serviceFile";
        }

        $localFile = "{$this->options['root']}src/$pack/config/$serviceFile";

        foreach ([$vendorFile, $localFile] as $file) {
            if($services = $this->fetchConfigFile($file)) {
                continue;
            }
        }

        if(!$services) throw new \Exception("Can't find services file $serviceFile in $pack");

        $this->addServices($services);

        return $this;
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

        return null;
    }

    function getPackList()
    {
        return $this->packList;
    }
}
