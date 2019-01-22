<?php
namespace Prim;

class Container
{
    protected $parameters = [];
    protected $options = [];

    static protected $shared = [];

    public function __construct(array $parameters = [], array $options = [])
    {
        $this->parameters = $parameters += [
            'application.class' => 'Prim\Application',
            'view.class' => 'Prim\View',
            'router.class' => 'Prim\Router',
            'pdo.class' => 'PDO',
            'packList.class' => 'Prim\PackList',
            'errorController.class' => 'PrimPack\Controller\Error',
            'service.class' => 'Prim\Service'
        ];

        $this->options = $options += [
            'root' => ''
        ];
    }

    protected function init(string $name, ...$args): object
    {
        if (isset(self::$shared[$name]))
        {
            return self::$shared[$name];
        }

        $services = $this->getService()->getServicesInjection($name);

        if($services) $args = array_merge($args, $services);

        $class = $this->parameters["$name.class"];

        $obj = new $class(...$args);

        return self::$shared[$name] = $obj;
    }

    protected function setDefaultParameter(string $obj, string $class): void
    {
        if(!isset($this->parameters["$obj.class"])) {
            $this->parameters["$obj.class"] = $class;
        }
    }

    /**
     * @return Application
     */
    public function getApplication(): object
    {
        $obj = 'application';

        return $this->init($obj, $this, $this->options);
    }

    /**
     * @return Router
     */
    public function getRouter(): object
    {
        $obj = 'router';

        return $this->init($obj, $this, $this->options);
    }

    /**
     * @return Service
     */
    public function getService(): object
    {
        $name = 'service';

        if (isset(self::$shared[$name]))
        {
            return self::$shared[$name];
        }

        $class = $this->parameters["$name.class"];

        $obj = new $class($this, $this->getPackList(), $this->options);

        return self::$shared[$name] = $obj;
    }


    /**
     * @return View
     */
    public function getView(): object
    {
        $obj = 'view';

        return $this->init($obj, $this, $this->options);
    }

    /**
     * @return Controller
     */
    public function getController(string $obj): object
    {
        if(!isset($this->parameters["$obj.class"])) {
            $this->parameters["$obj.class"] = $obj;
        }

        return $this->init($obj, $this->getView(), $this, $this->options);
    }

    /**
     * @return Controller
     */
    public function getErrorController(): object
    {
        return $this->getController('errorController');
    }

    /**
     * @return Model
     */
    public function getModel(string $obj): object
    {
        $this->parameters["$obj.class"] = $obj;

        return $this->init($obj, $this->getPDO(), $this->options);
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

    /**
     * @return \PDO
     */
    public function getPDO(string $type = '', string $host = '', string $name = '', string $user = '', string $pass = '', array $options = [], string $charset = 'utf8'): object
    {
        $obj = 'pdo';

        if(!$this->options['db_enable']) {
            throw new \Exception('The database is disabled in the configuration file but a service try to access it!');
        }

        $args = "$type:host=$host;dbname=$name";

        if($type !== 'pgsql') {
            $args .= ";charset=$charset";
        } else {
            $options += [\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES $charset"];
        }

        return $this->init($obj, $args, $user, $pass, $options);
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public function getComposer(): object
    {
        $name = 'composer.class';

        if (isset(self::$shared[$name]))
        {
            return self::$shared[$name];
        }

        $composer = "{$this->options['root']}vendor/autoload.php";

        if(!file_exists($composer)) {
            throw new \Exception("Couldn't get composer");
        }

        return self::$shared[$name] = require $composer;
    }

    /**
     * @return PackList
     */
    public function getPackList(): object
    {
        $name = 'packList';

        if (isset(self::$shared[$name]))
        {
            return self::$shared[$name];
        }

        $class = $this->parameters["$name.class"];

        $obj = new $class($this->getComposer(), $this->options);

        return self::$shared[$name] = $obj;
    }
}