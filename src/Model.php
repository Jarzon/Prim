<?php declare(strict_types=1);
namespace Prim;

use Exception;
use PrimPack\Service\PDO;
use PrimPack\Service\PDOStatement;

class Model
{
    /* @phpstan-ignore-next-line */
    public PDO $db;
    /** @var array<mixed> $options */
    protected array $options = [];

    /** @param array<mixed> $options */
    function __construct(
        /* @phpstan-ignore-next-line */
        PDO $db,
        array $options = []
    ) {
        $this->db = $db;
        $this->options = $options += [];
    }

    public function getClassName(string $classname): string
    {
        if ($pos = strrpos($classname, '\\')) return lcfirst(substr($classname, $pos + 1));
        return '';
    }

    public function setOption(string $name, mixed $value): void
    {
        $this->options[$name] = $value;
    }

    /* @phpstan-ignore-next-line */
    public function prepare(string $statement, array $driver_options = []): PDOStatement
    {
        /* @phpstan-ignore-next-line */
        return $this->db->prepare($statement, $driver_options);
    }

    /**
     * @param array<mixed> $data
     * @param array<mixed> $whereValues
     */
    public function update(string $table, array $data, string $where = '', array $whereValues = []): int
    {
        $values = array_values($data);

        if($where !== '') {
            $where = "WHERE $where";
            $values = array_merge($values, $whereValues);
        }

        $query = "UPDATE $table SET ".implode('=?,', array_keys($data))."=? $where";

        $statement = $this->prepare($query);

        /* @phpstan-ignore-next-line */
        if($statement->execute($values)) {
            /* @phpstan-ignore-next-line */
            return $statement->rowCount();
        }

        $valuesString = var_export($values, true);

        throw new Exception("Model->update() on $table table failed.<br>
        Query: $query<br>
        Params: $valuesString");
    }

    /** @param array<mixed> $data */
    public function insert(string $table, array $data): int
    {
        $columns = implode(',', array_keys($data));
        $placeholders = implode(',', str_split(str_repeat('?', sizeof($data))));
        $values = array_values($data);

        $query = "INSERT INTO $table ($columns) VALUES($placeholders)";

        $statement = $this->prepare($query);

        /* @phpstan-ignore-next-line */
        if($statement->execute($values)) {
            /* @phpstan-ignore-next-line */
            return (int) $this->db->lastInsertId();
        }

        $valuesString = var_export($data, true);

        throw new Exception("Model->insert() on $table table failed.<br>
        Query: $query<br>
        Params: $valuesString");
    }
}
