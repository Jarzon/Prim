<?php
namespace Prim;

class Model
{

    function __construct(\PDO $db)
    {
        $this->db = $db;
    }

}
