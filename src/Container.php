<?php
namespace Prim;

// TODO: Move more to the Trait for code reuse
class Container
{
    static protected $shared = array();

    public function __construct(array $parameters = [
        'view.class' => '\\Prim\\View',
        'router.class' => '\\Prim\\Router'
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

    public function getRouter()
    {
        $obj = 'router';

        return $this->init($obj);
    }

    public function getView()
    {
        $obj = 'view';

        return $this->init($obj);
    }

    public function getController(string $obj)
    {
        $this->parameters["$obj.class"] = $obj;

        return $this->init($obj, $this->getView());
    }
}