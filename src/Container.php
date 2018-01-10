<?php
namespace Prim;

class Container
{
    static protected $shared = array();

    public function __construct(array $parameters = [
        'view.class' => '\\Prim\\View',
        'router.class' => '\\Prim\\Router',
        'pdo.class' => '\PDO',
        'errorController.class' => 'PrimPack\Controller\Error'
    ])
    {
        $this->parameters = $parameters;
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

    public function getRouter(\FastRoute\RouteCollector $router)
    {
        $obj = 'router';

        return $this->init($obj, $router);
    }

    public function getView()
    {
        $obj = 'view';

        return $this->init($obj, $this);
    }

    public function getController(string $obj)
    {
        $this->parameters["$obj.class"] = $obj;

        return $this->init($obj, $this->getView(), $this);
    }

    public function getErrorController()
    {
        $obj = 'errorController';

        return $this->init($obj, $this->getView(), $this);
    }

    public function getModel(string $obj)
    {
        $this->parameters["$obj.class"] = $obj;

        return $this->init($obj, $this);
    }

    public function getPDO(string $type = '', string $host = '', string $name = '', string $charset = '', string $user = '', string $pass = '', array $options = [])
    {
        $obj = 'pdo';

        return $this->init($obj, "$type:host=$host;dbname=$name;charset=$charset", $user, $pass, $options);
    }
}