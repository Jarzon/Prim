<?php
namespace Prim;

class View implements ViewInterface
{
    public $root = ROOT;

    protected $container;

    protected $design = 'design';
    protected $designPack = 'BasePack';
    protected $pack = '';

    protected $vars = [];

    protected $sections = [];
    protected $section = 'default';
    protected $sectionPush = false;

    public function __construct(Container $container)
    {
        $this->container = $container;

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

    function design(string $view, string $packDirectory = '', array $vars = [])
    {
        $this->renderTemplate($view, $packDirectory, $vars, true, true);
    }

    function render(string $view, string $packDirectory = '', array $vars = [], bool $template = true)
    {
        $this->renderTemplate($view, $packDirectory, $vars, $template, false);
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
        if(empty($vars)) {
            return $this->vars;
        }

        $this->vars = array_merge($this->vars, $vars);
    }

    function renderTemplate(string $view, string $packDirectory = '', array $vars = [], bool $template = true, bool $default = false)
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

        try {
            if($default) $this->start('default');

            // TODO: Move this in a method that detect if the file exist and return the correct string
            $viewFile = "{$this->root}src/$packDirectory/view/$view.php";
            if(file_exists($viewFile)) {
                include($viewFile);
            } else {
                include("{$this->root}vendor/".strtolower($packDirectory)."/view/$view.php");
            }

            if($default) $this->end();
        } catch (\Exception $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }

            throw $e;
        }

        if ($template) {
            $viewFile = "{$this->root}src/{$this->designPack}/view/_templates/{$this->design}.php";
            if(file_exists($viewFile)) {
                include($viewFile);
            } else {
                include("{$this->root}vendor/".strtolower($this->designPack)."/view/_templates/{$this->design}.php");
            }
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

    public function insert(string $name, string $pack = '', array $vars = [])
    {
        echo $this->renderTemplate($name, $pack, $vars, false, false);
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
        return "$name?v=" . hash_file('fnv1a32', "{$this->root}public$name");
    }

    function fileCache($name) {
        return "$name?v=" . filemtime( "{$this->root}public/$name");
    }

    public function toolbar() {
        if(DEBUG):
            function formatBytes($bytes, $precision = 2) {
                $units = array('B', 'KB', 'MB', 'GB', 'TB');

                $bytes = max($bytes, 0);
                $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
                $pow = min($pow, count($units) - 1);

                // Uncomment one of the following alternatives
                $bytes /= pow(1024, $pow);

                return round($bytes, $precision) . ' ' . $units[$pow];
            }
            ?>
            <style>
                .primDebug .logo, .primDebug .time, .primDebug .memory {
                    font-size: 20px;
                    float: left;
                    margin: 5px 5px 0 20px;
                }

                .primDebug .time {
                    min-width: 110px;
                }

                .primDebug .memory {
                    min-width: 230px;
                }

                .primDebug {
                    position: fixed;
                    bottom: 0;
                    left: 20px;
                    background: #333;
                    z-index: 999;
                }
            </style>
            <div class="primDebug">
                <div class="logo">Prim</div>


                <div class="time">Time: <?=floor(xdebug_time_index() * 1000)?> ms</div>
                <div class="memory">Memory: <?=formatBytes(xdebug_memory_usage())?> / <?=formatBytes(xdebug_peak_memory_usage())?></div>
            </div>

        <?php endif;
    }
}
