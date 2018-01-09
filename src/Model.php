<?php
namespace Prim;

class Model
{
    /**
     * @var Container $container
     * @var \PDO $db
     */
    public $container;
    public $db;

    function __construct($container)
    {
        $this->container = $container;
        $this->db = $container->getPDO();
    }

    public function prepare(string $statement, array $driver_options = [])
    {
        return $this->db->prepare($statement, $driver_options);
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
        $placeholders = implode(',', str_split(str_repeat('?', sizeof($data))));
        $values = array_values($data);

        $query = $this->db->prepare("INSERT INTO $table ($columns) VALUES($placeholders)");

        $query->execute($values);
    }

    protected function convertDate(&$data, $index, $format = 'Y-m-d') {
        if(!empty($data[$index])) {
            $data[$index] = str_replace('/', '-', $data[$index]);
            $data[$index] = date($format, strtotime($data[$index]));
        }
    }
}
