<?php
namespace Tests\Mocks;

class Service extends \Prim\Service
{
    public function __construct($container, array $options = [], $packList = null)
    {
        $this->options = $options += [
            'root' => ''
        ];
    }
}