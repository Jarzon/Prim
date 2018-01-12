<?php
namespace Prim;

class Container
{
    static protected $shared = array();

    public function __construct(array $parameters = [])
    {
        $this->parameters = array_merge([
            'view.class' => '\Prim\View',
            'router.class' => '\Prim\Router',
            'pdo.class' => '\PDO',
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
     * @return Router
     */
    public function getRouter(\FastRoute\RouteCollector $router)
    {
        $obj = 'router';

        return $this->init($obj, $router);
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
}