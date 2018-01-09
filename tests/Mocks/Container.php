<?php
namespace Tests\Mocks;

class Container extends \Prim\Container
{
    public function __construct(array $parameters = [
        'view.class' => '\Tests\Mocks\View',
        'router.class' => '\\Prim\\Router',
        'pdo.class' => '\Tests\Mocks\PDO'
    ])
    {
        parent::__construct($parameters);
    }
}