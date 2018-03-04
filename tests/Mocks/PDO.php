<?php
namespace Tests\Mocks;

class PDO extends \PDO
{
    public $sql = '';

    public function __construct ()
    {}

    public function prepare($sql, $options = null) {
        $this->sql = $sql;

        $statement = new PDOStatement();

        return $statement;
    }

    public function lastInsertId($name = null)
    {
        return 1;
    }
}