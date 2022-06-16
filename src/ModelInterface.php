<?php
namespace Prim;

interface ModelInterface
{
    function getClassName(string $classname): string;

    function setOption(string $name, mixed $value): void;

    function prepare(string $statement, array $driver_options = []): object;

    function update(string $table, array $data, string $where = '', array $whereValues = []): int;

    function insert(string $table, array $data): int;
}