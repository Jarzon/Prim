<?php
namespace Prim;

class Container
{
    protected $serviceInjections = [];
    protected $serviceConfig = [];

    protected $parameters = [];
    protected $options = [];

    protected $service;

    static protected $shared = [];

    public function __construct(array $parameters = [], array $options = [], $service = null)
    {
        $this->parameters = $parameters;

        $this->options = $options += [
            'root' => '',
            'project_name' => '',

            'disable_services_injection' => false,

            'db' => [
                'enable' => false,
                'type' => 'mysql',
                'name' => $options['project_name']?? '',
                'host' => '127.0.0.1',
                'user' => 'root',
                'password' => '',
                'charset' => 'utf8',
                'options' => [],
            ]
        ];

        $this->service = $service ?: new Service($this, $this->options);
    }

    protected function init(string $name, array $args): object
    {
        if (isset(self::$shared[$name]))
        {
            return self::$shared[$name];
        }

        $class = $this->parameters[$name];

        if(!$this->options['disable_services_injection']) {
            $services = $this->service->getServicesInjection($class);

            if($services) $args = array_merge($args, $services);
        }

        $obj = new $class(...$args);

        if(isset($this->serviceConfig[$name])) {
            $this->serviceConfig[$name]($obj);
        }

        return self::$shared[$name] = $obj;
    }

    protected function setDefaultParameter(string $obj, string $class): void
    {
        if(!isset($this->parameters[$obj])) {
            $this->parameters[$obj] = $class;
        }
    }

    public function get($name)
    {
        if(isset($this->parameters[$name])) {
            $params = [];

            if(isset($this->serviceInjections[$name])) {
                $params = $this->serviceInjections[$name];
            }

            return $this->init($name, $params);
        }

        throw new \Exception("Can't find service $name");
    }

    public function register($name, string $location, ?array $injections, callable $config = null)
    {
        $this->setDefaultParameter($name, $location);

        if($injections) $this->serviceInjections[$name] = $injections;
        if($config) $this->serviceConfig[$name] = $config;
    }
}