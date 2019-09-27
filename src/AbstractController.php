<?php declare(strict_types=1);
namespace Prim;

abstract class AbstractController
{
    public $db;
    public $view;
    public $container;
    public $projectNamespace = '';
    public $packNamespace = '';

    protected $options = [];

    /**
     * @param View $view
     */
    function __construct($view, array $options = [])
    {
        $this->view = $view;

        $this->options = $options += [
            'root' => '/root/'
        ];

        $this->getNamespace(get_class($this));

        $this->view->setPack($this->packNamespace);
    }

    public function getClassName(string $classname): string
    {
        if ($pos = strrpos($classname, '\\')) return lcfirst(substr($classname, $pos + 1));
        return '';
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
    public function setTemplate(string $design, string $pack = ''): void
    {
        $this->view->setTemplate($design, $pack);
    }

    public function design(string $view, string $pack = '', array $vars = [])
    {
        $this->view->design($view, $pack, $vars);
    }

    public function render(string $view, string $pack = '', array $vars = [], bool $template = true)
    {
        $this->view->render($view, $pack, $vars, $template);
    }

    public function addVar(string $name, $var)
    {
        $this->view->addVar($name, $var);
    }

    public function addVars(array $vars)
    {
        $this->view->addVars($vars);
    }

    public function message(string $type, string $message, $args = []): void
    {
        $_SESSION['_flashMessage'] = [$type, $message, $args];
    }

    public function redirect(string $uri): void
    {
        header("location: $uri");
        exit;
    }
}
