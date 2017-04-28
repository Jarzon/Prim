<?php
namespace Prim;

interface ViewInterface
{
    /**
     * Set the default template
     */
    function setTemplate(string $design, string $pack);

    /**
     * Set the language
     */
    function setLanguage(string $language);

    /**
     * Fetch the template design to show the view in
     */
    function design(string $view);

    /**
     * Fetch a translation file and return an array that contain the messages
     */
    function fetchTranslation();

    /**
     * Add a var for the view
     * @param mixed $var
     */
    function addVar(string $name, $var);

    /**
     * Add a vars for the view
     */
    function addVars(array $vars);
}