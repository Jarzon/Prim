<?php
namespace Prim;

class View implements ViewInterface
{
    private $design = 'design';
    private $designPack = 'BasePack';
    private $language = 'en';
    private $messages = [];
    private $vars = [];
    private $root = ROOT;
    private $pack = '';
    private $sections = [];
    private $section = 'default';
    private $sectionPush = false;

    function setPack(string $pack)
    {
        $this->pack = $pack;
    }

    function setTemplate(string $design, string $pack)
    {
        $this->design = $design;
        $this->designPack = $pack;
    }

    function setLanguage(string $language)
    {
        $this->language = $language;
    }

    function design(string $view, string $packDirectory = '')
    {
        $this->renderTemplate($view, $packDirectory, true);
    }

    function render(string $view, string $packDirectory = '')
    {
        $this->renderTemplate($view, $packDirectory);
    }

    function renderTemplate(string $view, string $packDirectory = '', bool $default = false)
    {
        if($packDirectory == '') {
            $packDirectory = $this->pack;
        }

        // Create the view vars
        if(!empty($this->vars)) extract($this->vars);

        define('LANG_ROW', array_search($this->language, $this->messages['languages']));

        $_ = function(string $message) {
            return $this->messages[$message][LANG_ROW];
        };

        $e = function(string $string) {
            return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE);
        };

        $level = ob_get_level();
        ob_start();

        try {
            if($default) $this->start('default');
            require "{$this->root}src/$packDirectory/view/$view.php";
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

    function _getTranslation()
    {
        $file = $this->root . 'app/config/messages.json';

        // Check if we have a translation file for that language
        if (file_exists($file)) {
            // TODO: Cache the file
            $this->messages = json_decode(file_get_contents($file), true);
        }
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
