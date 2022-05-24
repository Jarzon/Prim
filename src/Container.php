<?php declare(strict_types=1);
namespace Prim;

use Exception;
use PDO;
use Prim\Console\Console;

class Container
{
    protected array $serviceInjections = [];

    protected array $parameters = [];
    public array $options = [];

    protected Service $service;

    static protected array $shared = [];

    public function __construct(array $options = [], $parameters = null, ?Service $service = null)
    {
        $projectConfig = include("{$options['app']}config/config.php");

        if($projectConfig === false) {
            echo 'Missing project config file';
            exit;
        }

        $options = $projectConfig + $options;

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

        $packslist = $this->service->getPackList();

        $this
            ->register('application', Application::class, [$this, $options])
            ->register('console', Console::class, [$this, $options['root']])
            ->register('view', View::class, [$packslist, $options])
            ->register('router', Router::class, [$this, $options])
            ->register('pdo', PDO::class, function (Container $dic) use($options) {
                if(!$this->options['db_enable']) {
                    throw new Exception('The database is disabled in the configuration file but a service try to access it!');
                }

                $path = "{$options['db_type']}:";

                if($options['db_type'] !== 'sqlite') {
                    $path .= "host={$options['db_host']};dbname={$options['db_name']}";
                }
                if($options['db_type'] !== 'pgsql') {
                    $path .= ";charset={$options['db_charset']}";
                }

                return $dic->init('pdo', [
                    $path,
                    $options['db_user'],
                    $options['db_password'],
                    $options['db_options']
                ]);
            })
            ->register('errorController', 'PrimPack\Controller\Error', function(Container $dic) use($options) {
                return $dic->init('errorController', [$dic->get('view'), $options]);
            })
            ->setObj('packlist', $packslist);

        if($parameters !== null) {
            $this->parameters = array_merge($this->parameters, $parameters);
        } else {
            $this->loadConfig();
        }
    }

    private function loadConfig(): void
    {
        include("{$this->options['root']}app/config/container.php");
    }

    function registerConfig(string $pack, string $configFile = 'container.php'): Container
    {
        if($vendorFile = $this->get('packlist')->getVendorPath($pack)) {
            $vendorFile = "{$this->options['root']}$vendorFile/config/$configFile";
        }

        $localFile = "{$this->options['root']}src/$pack/config/$configFile";

        $noContainer = true;
        foreach ([$vendorFile, $localFile] as $file) {
            if($this->fetchConfigFile($file)) {
                $noContainer = false;
                break;
            }
        }

        if($noContainer) throw new \Exception("Can't find container config files $configFile for $pack");

        return $this;
    }

    protected function fetchConfigFile(string $file): bool
    {
        if(file_exists($file)) {
            include($file);
            return true;
        }

        return false;
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

        throw new Exception("Can't find service $name");
    }

    public function register($name, string $location, $params = null): Container
    {
        $this->setParameter($name, $location);

        if($params) $this->serviceInjections[$name] = $params;

        return $this;
    }

    public function setObj($name, $obj): Container
    {
        self::$shared[$name] = $obj;

        return $this;
    }

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
            throw new Exception("Can't find model: $modelNamespace");
        }

        return $this->getModel($modelNamespace);
    }

    public function getEntity(string $name): object
    {
        if (isset(self::$shared[$name]))
        {
            return self::$shared[$name];
        }

        $this->setParameter($name, $name);

        return $this->init($name, []);
    }

    public function entity(string $entity): object
    {
        list($pack, $entity) = explode('\\', $entity);

        $entityNamespace = "$pack\\Entity\\$entity";

        $localNamespace = "{$this->options['project_name']}\\$entityNamespace";

        if(class_exists($localNamespace)) {
            $entityNamespace = $localNamespace;
        } else if(!class_exists($entityNamespace)) {
            throw new Exception("Can't find model: $entityNamespace");
        }

        return $this->getEntity($entityNamespace);
    }

    public function service(string $model): object
    {
        list($pack, $model) = explode('\\', $model);

        $modelNamespace = "$pack\\Service\\$model";

        $localNamespace = "{$this->options['project_name']}\\$modelNamespace";

        if(class_exists($localNamespace)) {
            $modelNamespace = $localNamespace;
        } else if(!class_exists($modelNamespace)) {
            throw new Exception("Can't find model: $modelNamespace");
        }

        return $this->getService($modelNamespace);
    }

    public function getService(string $name): object
    {
        if (isset(self::$shared[$name]))
        {
            return self::$shared[$name];
        }

        $this->setParameter($name, $name);

        return $this->init($name, []);
    }

    public function form(string $form): object
    {
        list($pack, $form) = explode('\\', $form);

        $modelNamespace = "$pack\\Form\\$form";

        $localNamespace = "{$this->options['project_name']}\\$modelNamespace";

        if(class_exists($localNamespace)) {
            $modelNamespace = $localNamespace;
        } else if(!class_exists($modelNamespace)) {
            throw new Exception("Can't find form: $modelNamespace");
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
