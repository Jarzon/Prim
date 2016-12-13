<?php
namespace Prim;

interface ViewInterface
{
    /**
     * Set the default template
     * @param string $design
     */
    function setTemplate($design);

    /**
     * Set the language
     * @param string $language
     */
    function setLanguage($language);

    /**
     * Fetch the template design to show the view in
     * @param string $view
     */
    function design($view);

    /**
     * Fetch a translation file and return an array that contain the messages
     */
    function _getTranslation();

    /**
     * Add a var for the view
     * @param string $name
     * @param mixed $var
     */
    function addVar($name, $var);

    /**
     * Add a vars for the view
     * @param array $vars
     */
    function addVars($vars);
}