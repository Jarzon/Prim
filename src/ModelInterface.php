<?php
namespace Prim;

interface ModelInterface
{
    function getClassName(string $classname): string;

    function setOption(string $name, mixed $value): void;
    /** @param array<mixed> $driver_options */

    function prepare(string $statement, array $driver_options = []): object;

    /**
     * @param array<mixed> $data
     * @param array<mixed> $whereValues
     */
    function update(string $table, array $data, string $where = '', array $whereValues = []): int;

    /** @param array<mixed> $data */
    function insert(string $table, array $data): int;
}