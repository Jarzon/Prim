<?php
namespace Prim;

interface ViewInterface
{
    function setTemplate(string $design, string $pack): void;

    function design(string $view, string $pack, array $vars = []): void;

    function render(string $view, string $pack, array $vars = [], bool $template = true): void;

    function addVar(string $name, mixed $var): void;

    function addVars(array $vars): void;
}