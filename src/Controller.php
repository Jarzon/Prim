<?php
namespace Prim;

class Controller implements ViewInterface
{
    public $db;
    public $view;
    public $container;
    public $projectNamespace = '';
    public $packNamespace = '';

    protected $options = [];

    /**
     * @param View $view
     * @param Container $container
     */
    function __construct($view, Container $container, array $options = [])
    {
        $this->view = $view;
        $this->container = $container;

        $this->options = $options += [
            'root' => '/root/'
        ];

        $this->getNamespace(get_class($this));

        $this->view->setPack($this->packNamespace);
    }

    function getClassName(string $classname): string
    {
        if ($pos = strrpos($classname, '\\')) return lcfirst(substr($classname, $pos + 1));
        return $pos;
    }

    public function getNamespace(string $namespaces): void
    {
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

    // View Methods shortcut
    function setTemplate(string $design, string $pack = ''): void
    {
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

    function addVar(string $name, $var)
    {
        $this->view->addVar($name, $var);
    }

    function addVars(array $vars)
    {
        $this->view->addVars($vars);
    }

    function message(string $type, $message)
    {
        $_SESSION['_flashMessage'] = [$type, $message];
    }

    function redirect(string $uri)
    {
        header("location: $uri");
        exit;
    }
}