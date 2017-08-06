<?php
namespace Prim;

class View implements ViewInterface
{
    protected $root = ROOT;

    protected $design = 'design';
    protected $designPack = 'BasePack';
    protected $pack = '';

    protected $vars = [];

    protected $sections = [];
    protected $section = 'default';
    protected $sectionPush = false;

    public function __construct()
    {
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

    function setPack(string $pack)
    {
        $this->pack = $pack;
    }

    function setTemplate(string $design, string $pack)
    {
        $this->design = $design;
        $this->designPack = $pack;
    }

    function design(string $view, string $packDirectory = '')
    {
        $this->renderTemplate($view, $packDirectory, true);
    }

    function render(string $view, string $packDirectory = '', array $vars = [])
    {
        $this->renderTemplate($view, $packDirectory, false, $vars);
    }

    function escape(string $string) : string
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE);
    }

    function registerFunction(string $name, \Closure $closure)
    {
        if(!isset($this->vars[$name])) $this->vars[$name] = $closure;
    }

    function vars(array $vars = [])
    {
        if(!empty($vars)) {
            return $this->vars;
        }

        $this->vars = array_merge($this->vars, $vars);
    }

    function renderTemplate(string $view, string $packDirectory = '', bool $default = false, array $vars = [])
    {
        $this->vars($vars);
        unset($vars);
        extract($this->vars);

        if($packDirectory == '') {
            $packDirectory = $this->pack;
        }

        $this->registerFunction('e', function(string $string) {
            return $this->escape($string);
        });

        $level = ob_get_level();
        ob_start();

        try {
            if($default) $this->start('default');

            $viewFile = "{$this->root}src/$packDirectory/view/$view.php";
            if(file_exists($viewFile)) {
                include($viewFile);
            } else {
                include("{$this->root}vendor/".strtolower($packDirectory)."/view/$view.php");
            }

            if($default) $this->end();
        } catch (Exception $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }

            throw $e;
        }

        $viewFile = "{$this->root}src/{$this->designPack}/view/_templates/{$this->design}.php";
        if(file_exists($viewFile)) {
            include($viewFile);
        } else {
            include("{$this->root}vendor/".strtolower($this->designPack)."/view/_templates/{$this->design}.php");
        }
    }

    function push(string $section)
    {
        $this->start($section);
        $this->sectionPush = true;
    }

    function start(string $section)
    {
        $this->section = $section;
        ob_start();
    }

    function end()
    {
        if($this->sectionPush) $this->sections[$this->section] .= ob_get_clean();
        else $this->sections[$this->section] = ob_get_clean();

        $this->sectionPush = false;
        $this->section = 'default';
    }

    /**
     * Return the content of a section
     */
    function section(string $section) : string
    {
        return isset($this->sections[$section])? $this->sections[$section]: '';
    }

    public function insert(string $name, string $pack, array $data = [])
    {
        echo $this->renderTemplate($name, $pack, $data);
    }

    function addVar(string $name, $var)
    {
        $this->vars[$name] = $var;
    }

    function addVars(array $vars)
    {
        foreach($vars as $var) {
            $this->addVar($var[0], $var[1]);
        }
    }

    function fileHash($name) {
        return "$name?" . hash_file('fnv1a32', "{$this->root}public/$name");
    }
}
