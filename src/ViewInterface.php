<?php
namespace Prim;

interface ViewInterface
{
    function setTemplate(string $design, string $pack): void;

    /** @param Array<mixed> $vars */
    function design(string $view, string $pack, array $vars = []): void;

    /** @param Array<mixed> $vars */
    function render(string $view, string $pack, array $vars = [], bool $template = true): void;

    function addVar(string $name, mixed $var): void;

    /** @param Array<mixed> $vars */
    function addVars(array $vars): void;
}