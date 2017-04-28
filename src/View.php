<?php
namespace Prim;

class View implements ViewInterface
{
    protected $root = ROOT;

    protected $design = 'design';
    protected $designPack = 'BasePack';
    protected $pack = '';

    protected $language = 'en';
    static protected $messagesLanguage = '';
    protected $messages = [];

    protected $vars = [];

    protected $sections = [];
    protected $section = 'default';
    protected $sectionPush = false;

    function __construct()
    {
        $this->fetchTranslation();
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

    function getLanguage() : string
    {
        return $this->language;
    }

    function setLanguage(string $language)
    {
        $this->language = $language;
    }

    function fetchTranslation()
    {
        $file = $this->root . 'app/config/messages.json';

        // Check if we have a translation file for that language
        if (file_exists($file)) {
            // TODO: Cache the file
            $this->messages = json_decode(file_get_contents($file), true);
        }
    }

    function design(string $view, string $packDirectory = '')
    {
        $this->renderTemplate($view, $packDirectory, true);
    }

    function render(string $view, string $packDirectory = '')
    {
        $this->renderTemplate($view, $packDirectory);
    }

    function translate(string $message) : string
    {
        return $this->messages[$message][self::$messagesLanguage];
    }

    function escape(string $string) : string
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE);
    }

    function renderTemplate(string $view, string $packDirectory = '', bool $default = false)
    {
        if($packDirectory == '') {
            $packDirectory = $this->pack;
        }

        $_ = $trans = $translate = function(string $message) {
            return $this->translate($message);
        };

        $e = $esc = $escape = function(string $string) {
            return $this->escape($string);
        };

        // Create the view vars
        if(!empty($this->vars)) extract($this->vars);

        if(self::$messagesLanguage === '') {
            self::$messagesLanguage = array_search($this->language, $this->messages['languages']);
        }

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
