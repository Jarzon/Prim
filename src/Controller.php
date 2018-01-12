<?php
namespace Prim;

class Controller implements ViewInterface
{
    public $db;
    public $view;
    public $container;
    public $projectNamespace = '';
    public $packNamespace = '';

    /**
     * @param View $view
     * @param Container $container
     */
    function __construct($view, $container)
    {
        $this->view = $view;
        $this->container = $container;

        $this->getNamespace(get_class($this));

        $this->view->setPack($this->packNamespace);

        $class_methods = get_class_methods($this);

        /*
         * All methods that start by build get automatically executed when the object is instantiated
         * */
        foreach ($class_methods as $method_name) {
            if (strpos($method_name, 'build') !== false) {
                $this->$method_name();
            }
        }
    }

    public function getNamespace(string $namespaces) {
        $namespaces = explode('\\', $namespaces);

        $pack = '';
        $project = '';

        foreach($namespaces as $namespace) {
            if(strpos($namespace, 'Pack')) {
                $pack = $namespace;
            } else if($project == '')  {
                if($pack != '') {
                    break;
                }
                $project = $namespace;
            }
        }

        $this->projectNamespace = $project;
        $this->packNamespace = $pack;
    }

    public function getModel(string $model, string $pack = '')
    {
        if($pack === '') $pack = $this->packNamespace;

        $modelNamespace = "$pack\\Model\\$model";

        if(class_exists("$this->projectNamespace\\$modelNamespace")) {
            $modelNamespace = "$this->projectNamespace\\$modelNamespace";
        } else if(!class_exists($modelNamespace)) {
            throw new \Exception("Can't find model: $modelNamespace");
        }

        return $this->container->getModel($modelNamespace);
    }

    // View Methods shortcut
    function setTemplate(string $design, string $pack = '') {
        $this->view->setTemplate($design, $pack);
    }

    function design(string $view, string $pack = '', array $vars = [])
    {
        $this->view->design($view, $pack, $vars);
    }

    function render(string $view, string $pack = '', array $vars = [], bool $template = true)
    {
        $this->view->render($view, $pack, $vars, $template);
    }

    function addVar(string $name, $var) {
        $this->view->addVar($name, $var);
    }

    function addVars(array $vars) {
        $this->view->addVars($vars);
    }

    function redirect(string $uri) {
        header("location: $uri");
        exit;
    }
}