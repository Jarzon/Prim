<?php declare(strict_types=1);
namespace Prim;

abstract class AbstractController
{
    public View $view;
    public string $projectNamespace = '';
    public string $packNamespace = '';

    protected array $options = [];

    function __construct(View $view, array $options = [])
    {
        $this->view = $view;

        $this->options = $options += [
            'root' => '/root/'
        ];

        $this->getNamespace(get_class($this));

        $this->view->setPack($this->packNamespace);
    }

    protected function cacheStaticPage(string $pageName, callable $pageCodeCallback): void
    {
        $cachedFile = "{$this->options['root']}/public/$pageName";

        if(file_exists($cachedFile)) {
            echo file_get_contents($cachedFile);
            exit;
        }

        $pageCodeCallback();

        if($this->options['environment'] !== 'prod') {
            file_put_contents($cachedFile, preg_replace('~^([ \t\n]+)~m', '', ob_get_contents()));
        }
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

    public function design(string $view, string $pack = '', array $vars = []): void
    {
        $this->view->design($view, $pack, $vars);
    }

    public function render(string $view, string $pack = '', array $vars = [], bool $template = true): void
    {
        $this->view->render($view, $pack, $vars, $template);
    }

    public function addVar(string $name, $var): void
    {
        $this->view->addVar($name, $var);
    }

    public function addVars(array $vars): void
    {
        $this->view->addVars($vars);
    }

    public function message(string $type, string $message, ...$args): void
    {
        $_SESSION['_flashMessage'] = [$type, $message, $args];
    }

    public function redirect(string $uri): never
    {
        header("location: $uri");
        exit;
    }
}
