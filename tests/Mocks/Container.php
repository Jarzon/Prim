<?php
namespace Tests\Mocks;

use Prim\Service;

class Container extends \Prim\Container
{
    public function __construct(array $options = [], $parameters = null, ?Service $service = null)
    {
        parent::__construct([], [], null);
    }

    public function getComposer(): object
    {
        return new Composer();
    }
}
