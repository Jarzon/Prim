<?php
namespace Prim;

use Prim\Console\Console;

class Container
{
    protected $serviceInjections = [];

    protected $parameters = [];
    protected $options = [];

    protected $service;

    static protected $shared = [];

    public function __construct(array $options = [], $parameters = null, $service = null)
    {
        $this->options = $options += [
            'root' => '',
            'project_name' => '',

            'disable_services_injection' => false,

            'db_enable' => false,
            'db_type' => 'mysql',
            'db_name' => $options['project_name']?? '',
            'db_host' => '127.0.0.1',
            'db_user' => 'root',
            'db_password' => '',
            'db_charset' => 'utf8',
            'db_options' => [],
        ];

        $this->service = $service ?: new Service($this, $this->options);

        $this
            ->register('application', Application::class, [$this, $options])
            ->register('console', Console::class, [$this, $options['root']])
            ->register('view', View::class, [$this, $options])
            ->register('router', Router::class, [$this, $options])
            ->register('pdo', \PDO::class, function (Container $container) use($options) {
                if(!$this->options['db_enable']) {
                    throw new \Exception('The database is disabled in the configuration file but a service try to access it!');
                }

                return $container->init('pdo', [
                    "{$options['db_type']}:host={$options['db_host']};dbname={$options['db_name']}" . ($options['db_type'] !== 'pgsql'? ";charset={$options['db_charset']}": ''),
                    $options['db_user'],
                    $options['db_password'],
                    $options['db_options']
                ]);
            })
            ->register('errorController', 'PrimPack\Controller\Error', [$this->get('view'), $options])
            ->setObj('packlist', $this->service->getPackList());

        if($parameters !== null) {
            $this->parameters += $parameters;
        } else {
            $this->loadConfig();
        }
    }

    function loadConfig() {
        include("{$this->options['root']}app/config/container.php");
    }

    public function init(string $name, $args): object
    {
        if(!is_array($args)) {
            $args = (array)$args;
        }

        $class = $this->parameters[$name];

        if(!$this->options['disable_services_injection']) {
            $services = $this->service->getServicesInjection($class);

            if($services) $args = array_merge($args, $services);
        }

        $obj = new $class(...$args);

        return self::$shared[$name] = $obj;
    }

    protected function setParameter(string $obj, string $class)
    {
        $this->parameters[$obj] = $class;

        return $this;
    }

    public function get($name)
    {
        if (isset(self::$shared[$name]))
        {
            return self::$shared[$name];
        }

        if(isset($this->parameters[$name])) {
            $params = [];

            if(isset($this->serviceInjections[$name])) {
                if(is_callable($this->serviceInjections[$name])) {
                    return $this->serviceInjections[$name]($this);
                } else {
                    $params = $this->serviceInjections[$name];
                }
            }

            return $this->init($name, $params);
        }

        throw new \Exception("Can't find service $name");
    }

    public function register($name, string $location, $params = null)
    {
        $this->setParameter($name, $location);

        if($params) $this->serviceInjections[$name] = $params;

        return $this;
    }

    public function setObj($name, $obj)
    {
        self::$shared[$name] = $obj;

        return $this;
    }

    /**
     * @return Model
     */
    public function getController(string $name): object
    {
        if (isset(self::$shared[$name]))
        {
            return self::$shared[$name];
        }

        $this->setParameter($name, $name);

        return $this->init($name, [$this->get('view'), $this->options]);
    }

    /**
     * @return Model
     */
    public function getModel(string $name): object
    {
        if (isset(self::$shared[$name]))
        {
            return self::$shared[$name];
        }

        $this->setParameter($name, $name);

        return $this->init($name, [$this->get('pdo'), $this->options]);
    }

    public function model(string $model): object
    {
        list($pack, $model) = explode('\\', $model);

        $modelNamespace = "$pack\\Model\\$model";

        $localNamespace = "{$this->options['project_name']}\\$modelNamespace";

        if(class_exists($localNamespace)) {
            $modelNamespace = $localNamespace;
        } else if(!class_exists($modelNamespace)) {
            throw new \Exception("Can't find model: $modelNamespace");
        }

        return $this->getModel($modelNamespace);
    }

    public function form(string $form): object
    {
        list($pack, $form) = explode('\\', $form);

        $modelNamespace = "$pack\\Form\\$form";

        $localNamespace = "{$this->options['project_name']}\\$modelNamespace";

        if(class_exists($localNamespace)) {
            $modelNamespace = $localNamespace;
        } else if(!class_exists($modelNamespace)) {
            throw new \Exception("Can't find form: $modelNamespace");
        }

        return $this->getForm($modelNamespace);
    }

    public function getForm(string $name): object
    {
        if (isset(self::$shared[$name]))
        {
            return self::$shared[$name];
        }

        $this->setParameter($name, $name);

        return $this->init($name, []);
    }

    public function getCommand(string $name, ...$args): object
    {
        if (isset(self::$shared[$name]))
        {
            return self::$shared[$name];
        }

        $this->setParameter($name, $name);

        return $this->init($name, array_merge([$this->options], $args));
    }
}
