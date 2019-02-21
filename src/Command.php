<?php
namespace Prim;

class Command
{
    protected $name;
    protected $desc;

    protected function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    protected function setDescription(string $desc)
    {
        $this->desc = $desc;

        return $this;
    }

    public function getDescription()
    {
        return $this->desc;
    }

    public function getSignature()
    {
        return $this->getName();
    }

    public function exec()
    {
        throw new \Exception("Unimplemented command exec method.");
    }
}