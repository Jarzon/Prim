<?php declare(strict_types=1);
namespace Prim;

use Exception;

class Model
{
    public $db;
    protected array $options = [];

    /**
     * @param \PDO $db
     */
    function __construct($db, array $options = [])
    {
        $this->db = $db;
        $this->options = $options += [

        ];
    }

    public function getClassName(string $classname): string
    {
        if ($pos = strrpos($classname, '\\')) return lcfirst(substr($classname, $pos + 1));
        return '';
    }

    public function setOption($name, $value): void
    {
        $this->options[$name] = $value;
    }

    /** @return \PDOStatement */
    public function prepare(string $statement, array $driver_options = []): object
    {
        return $this->db->prepare($statement, $driver_options);
    }

    public function update(string $table, array $data, string $where = '', array $whereValues = []): int
    {
        $values = array_values($data);

        if($where !== '') {
            $where = "WHERE $where";
            $values = array_merge($values, $whereValues);
        }

        $query = "UPDATE $table SET ".implode('=?,', array_keys($data))."=? $where";

        $statement = $this->prepare($query);

        if($statement->execute($values)) {
            return $statement->rowCount();
        }

        $valuesString = var_export($values, true);

        throw new Exception("Model->update() on $table table failed.<br>
        Query: $query<br>
        Params: $valuesString");
    }

    public function insert(string $table, array $data): int
    {
        $columns = implode(',', array_keys($data));
        $placeholders = implode(',', str_split(str_repeat('?', sizeof($data))));
        $values = array_values($data);

        $query = "INSERT INTO $table ($columns) VALUES($placeholders)";

        $statement = $this->prepare($query);

        if($statement->execute($values)) {
            return (int) $this->db->lastInsertId();
        }

        $valuesString = var_export($data, true);

        throw new Exception("Model->insert() on $table table failed.<br>
        Query: $query<br>
        Params: $valuesString");
    }
}
