<?php
namespace Tests\Mocks;

class Container extends \Prim\Container
{
    public function getComposer(): object
    {
        return new Composer();
    }
}
