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
            'errorController.class' => 'PrimPack\Controller\Error'
        ];

        $this->options = $options += [
            'root' => ''
        ];
    }

    protected function init(string $name, ...$args) : object
    {
        if (isset(self::$shared[$name]))
        {
            return self::$shared[$name];
        }

        $class = $this->parameters["$name.class"];

        $obj = new $class(...$args);

        return self::$shared[$name] = $obj;
    }

    protected function setDefaultParameter(string $obj, string $class) : void
    {
        if(!isset($this->parameters["$obj.class"])) {
            $this->parameters["$obj.class"] = $class;
        }
    }

    /**
     * @return Application
     */
    public function getApplication() : object
    {
        $obj = 'application';

        return $this->init($obj, $this, $this->options);
    }

    /**
     * @param \FastRoute\RouteCollector $router
     * @return Router
     */
    public function getRouter($router = null) : object
    {
        $obj = 'router';

        return $this->init($obj, $router, $this, $this->options);
    }

    /**
     * @return View
     */
    public function getView() : object
    {
        $obj = 'view';

        return $this->init($obj, $this, $this->options);
    }

    /**
     * @return Controller
     */
    public function getController(string $obj) : object
    {
        $this->parameters["$obj.class"] = $obj;

        return $this->init($obj, $this->getView(), $this, $this->options);
    }

    /**
     * @return Controller
     */
    public function getErrorController() : object
    {
        $obj = 'errorController';

        return $this->init($obj, $this->getView(), $this, $this->options);
    }

    /**
     * @return Model
     */
    public function getModel(string $obj) : object
    {
        $this->parameters["$obj.class"] = $obj;

        return $this->init($obj, $this->getPDO(), $this->options);
    }

    /**
     * @return \PDO
     */
    public function getPDO(string $type = '', string $host = '', string $name = '', string $charset = '', string $user = '', string $pass = '', array $options = []) : object
    {
        $obj = 'pdo';

        if(!$this->options['db_enable']) {
            throw new \Exception('The database is disabled in the configuration file but a service try to access it!');
        }

        return $this->init($obj, "$type:host=$host;dbname=$name;charset=$charset", $user, $pass, $options);
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public function getComposer() : object
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
    public function getPackList() : object
    {
        $obj = 'packList';

        return $this->init($obj, $this->getComposer(), $this->options);
    }
}