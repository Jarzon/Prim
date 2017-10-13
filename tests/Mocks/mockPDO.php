<?php
namespace Tests\Mocks;

class mockPDO extends \PDO
{
    public $sql = '';

    public function __construct ()
    {}

    public function prepare($sql, $options = null) {
        $this->sql = $sql;

        $statement = new \PDOStatement();

        return $statement;
    }

    public function execute(array $values) {

    }
}