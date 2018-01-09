<?php
namespace Prim;

use \PDO;

class Controller implements ViewInterface
{
    /**
     * @var PDO $db
     * @var string $model
     * @var ViewInterface $view
     */
    public $db;
    public $model;
    public $view;
    public $container;
    public $projectNamespace;
    public $packNamespace;

    function __construct(ViewInterface $view, Container $container)
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

        $namespace = '';

        if(file_exists(ROOT . "src/$pack/Model/$model.php")) {
            $namespace = $this->projectNamespace.'\\';
        }

        $namespace .= "$pack\\Model\\$model";

        return $this->container->getModel($namespace);
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