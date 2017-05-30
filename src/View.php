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

    function render(string $view, string $packDirectory = '')
    {
        $this->renderTemplate($view, $packDirectory);
    }

    function escape(string $string) : string
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE);
    }

    function registerFunction(string $name, \Closure $closure)
    {
        if(!isset($this->vars[$name])) $this->vars[$name] = $closure;
    }

    function renderTemplate(string $view, string $packDirectory = '', bool $default = false)
    {
        if($packDirectory == '') {
            $packDirectory = $this->pack;
        }

        $this->registerFunction('e', function(string $string) {
            return $this->escape($string);
        });

        $viewPath = "$packDirectory/view/$view.php";

        // Create the view vars
        if(!empty($this->vars)) extract($this->vars);

        $level = ob_get_level();
        ob_start();

        try {
            if($default) $this->start('default');
            if(file_exists("{$this->root}vendor/$viewPath")) {
                include("{$this->root}vendor/$viewPath");
            } else {
                include("{$this->root}src/$viewPath");
            }
            if($default) $this->end();
        } catch (Exception $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }

            throw $e;
        }

        require "{$this->root}src/{$this->designPack}/view/_templates/{$this->design}.php";
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
    function section(string $section)
    {
        return isset($this->sections[$section])? $this->sections[$section]: '';
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
}
