<?php
namespace Prim;

interface ViewInterface
{
    /**
     * Set the default template
     */
    function setTemplate(string $design, string $pack);

    /**
     * Fetch the template design to show the view in
     */
    function design(string $view);

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