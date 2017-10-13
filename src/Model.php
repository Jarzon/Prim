<?php
namespace Prim;

class Model
{
    function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    public function update(string $table, array $data, string $where = '', array $whereValues = [])
    {
        $values = array_values($data);

        if($where !== '') {
            $where = "WHERE $where";
            $values = array_merge($values, $whereValues);
        }

        $query = $this->db->prepare("UPDATE $table SET ".implode('=?,', array_keys($data))."=? $where");

        $query->execute($values);
    }

    public function insert(string $table, array $data)
    {
        $columns = implode(',', array_keys($data));
        $placeholders = implode(',', str_split(str_repeat('?', sizeof($columns))));
        $values = implode(',', array_values($data));

        $query = $this->db->prepare("INSERT INTO $table ($columns) VALUES($placeholders)");

        $query->execute($values);
    }
}
