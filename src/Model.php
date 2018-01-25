<?php
namespace Prim;

class Model
{
    public $db;
    protected $container;

    /**
     * @param \PDO $db
     */
    function __construct($db)
    {
        $this->db = $db;
    }

    public function prepare(string $statement, array $driver_options = []) : object
    {
        return $this->db->prepare($statement, $driver_options);
    }

    public function update(string $table, array $data, string $where = '', array $whereValues = []) : int
    {
        $values = array_values($data);

        if($where !== '') {
            $where = "WHERE $where";
            $values = array_merge($values, $whereValues);
        }

        $query = $this->prepare("UPDATE $table SET ".implode('=?,', array_keys($data))."=? $where");

        $query->execute($values);

        return $query->rowCount();
    }

    public function insert(string $table, array $data) : int
    {
        $columns = implode(',', array_keys($data));
        $placeholders = implode(',', str_split(str_repeat('?', sizeof($data))));
        $values = array_values($data);

        $query = $this->prepare("INSERT INTO $table ($columns) VALUES($placeholders)");

        $query->execute($values);

        return (int) $this->db->lastInsertId();
    }

    protected function convertDate(array &$data, $index, string $format = 'Y-m-d') : void
    {
        if(!empty($data[$index])) {
            $data[$index] = str_replace('/', '-', $data[$index]);
            $data[$index] = date($format, strtotime($data[$index]));
        }
    }
}
