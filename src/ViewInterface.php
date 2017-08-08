<?php
namespace Prim;

interface ViewInterface
{
    function setTemplate(string $design, string $pack);

    function design(string $view, string $pack, array $vars = []);

    function render(string $view, string $pack, array $vars = [], bool $template = true);

    function addVar(string $name, $var);

    function addVars(array $vars);
}