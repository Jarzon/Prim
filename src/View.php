<?php
namespace Prim;

/**
 * @implements ViewInterface
 */
class View implements ViewInterface
{
    public $design = 'design';
    public $language = 'en';
    public $messages = [];
    public $vars = [];

    function setTemplate($design) {
        $this->design = $design;
    }

    function setLanguage($language) {
        $this->language = $language;
    }

    function design($view)
    {
        // Create the view vars
        if(!empty($this->vars)) extract($this->vars);

        define('LANG_ROW', array_search($this->language, $this->messages['languages']));

        $_ = function($message) {
            return $this->messages[$message][LANG_ROW];
        };

        require '../src/view/_templates/'.$this->design.'.php';
    }

    function _getTranslation()
    {
        $file = '../app/config/messages.json';

        // Check if we have a translation file for that language
        if (file_exists($file)) {
            // TODO: Cache the file
            $this->messages = json_decode(file_get_contents($file), true);
        }
    }

    function addVar($name, $var) {
        $this->vars[$name] = $var;
    }

    function addVars($vars) {
        foreach($vars as $var) {
            $this->addVar($var[0], $var[1]);
        }
    }
}