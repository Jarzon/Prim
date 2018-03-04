<?php
namespace Tests\Mocks;

class PDOStatement extends \PDOStatement
{
    public $sql = '';

    public function __construct ()
    {}

    public function execute($bound_input_params = NULL) {
        return true;
    }
}