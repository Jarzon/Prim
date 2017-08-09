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

    /**
     * Whenever controller is created, open a database connection too
     */
    function __construct(ViewInterface $view, Container $container)
    {
        if(DB_ENABLE) {
            $this->openDatabaseConnection(DB_TYPE, DB_HOST, DB_NAME, DB_CHARSET, DB_USER, DB_PASS);
        }

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

        return $this->container->getModel($namespace, $this->db);
    }

    /**
     * Open a Database connection using PDO
     */
    public function openDatabaseConnection(string $type, string $host, string $name, string $charset, string $user, string $pass)
    {
        // Set the fetch mode to object
        $options = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING);

        // generate a database connection, using the PDO connector
        $this->db = new PDO("$type:host=$host;dbname=$name;charset=$charset", $user, $pass, $options);
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