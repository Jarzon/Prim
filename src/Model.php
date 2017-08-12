<?php
namespace Prim;

class Model
{

    function __construct(\PDO $db)
    {
        try {
            $this->db = $db;
        } catch (\PDOException $e) {
            throw new Exception('Database connection could not be established.');
        }
    }

}
