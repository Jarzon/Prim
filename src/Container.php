<?php
namespace Prim;

class Container
{
    static protected $shared = array();

    public function __construct(array $parameters = [])
    {
        $this->parameters = array_merge([
            'application.class' => 'Prim\Application',
            'view.class' => 'Prim\View',
            'router.class' => 'Prim\Router',
            'pdo.class' => 'PDO',
            'packList.class' => 'Prim\PackList',
            'errorController.class' => 'PrimPack\Controller\Error'
        ], $parameters);
    }

    private function init(string $name, ...$args) {
        if (isset(self::$shared[$name]))
        {
            return self::$shared[$name];
        }

        $class = $this->parameters["$name.class"];

        $obj = new $class(...$args);

        return self::$shared[$name] = $obj;
    }

    /**
     * @return Application
     */
    public function getApplication()
    {
        $obj = 'application';

        return $this->init($obj, $this);
    }

    /**
     * @return Router
     */
    public function getRouter(\FastRoute\RouteCollector $router)
    {
        $obj = 'router';

        return $this->init($obj, $router, $this);
    }

    /**
     * @return View
     */
    public function getView()
    {
        $obj = 'view';

        return $this->init($obj, $this);
    }

    /**
     * @return Controller
     */
    public function getController(string $obj)
    {
        $this->parameters["$obj.class"] = $obj;

        return $this->init($obj, $this->getView(), $this);
    }

    /**
     * @return Controller
     */
    public function getErrorController()
    {
        $obj = 'errorController';

        return $this->init($obj, $this->getView(), $this);
    }

    /**
     * @return Model
     */
    public function getModel(string $obj)
    {
        $this->parameters["$obj.class"] = $obj;

        return $this->init($obj, $this);
    }

    /**
     * @return \PDO
     */
    public function getPDO(string $type = '', string $host = '', string $name = '', string $charset = '', string $user = '', string $pass = '', array $options = [])
    {
        $obj = 'pdo';

        return $this->init($obj, "$type:host=$host;dbname=$name;charset=$charset", $user, $pass, $options);
    }

    /**
     * @return Composer\Autoload\ClassLoader
     */
    public function getComposer()
    {
        $name = 'composer.class';

        if (isset(self::$shared[$name]))
        {
            return self::$shared[$name];
        }

        if($composer = require ROOT . 'vendor/autoload.php') {
            return self::$shared[$name] = $composer;
        }

        throw new \Exception("Couldn't get composer");
    }

    /**
     * @return PackList
     */
    public function getPackList()
    {
        $obj = 'packList';

        return $this->init($obj, $this->getComposer());
    }
}