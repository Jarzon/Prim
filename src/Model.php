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
        $columns = [];
        $values = [];

        foreach ($data as $i => $d) {
            $columns[] = "$i=?";
            $values[] = $d;
        }

        if($where !== '') {
            $where = "WHERE $where";
            array_merge($values, $whereValues);
        }

        $query = $this->db->prepare("UPDATE $table SET ".implode(',', $columns)." $where");

        $query->execute($values);
    }

    public function insert(string $table, array $data)
    {
        $columns = [];
        $values = [];

        foreach ($data as $i => $d) {
            $columns[] = $i;
            $values[] = $d;
        }

        $columns = implode(',', $columns);
        $placeholders = implode(',', str_split(str_repeat('?', sizeof($columns))));
        $values = implode(',', $values);

        $query = $this->db->prepare("INSERT INTO $table ($columns) VALUES($placeholders)");

        $query->execute($values);
    }
}
