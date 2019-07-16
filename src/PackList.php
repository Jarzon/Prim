<?php
namespace Prim;

class PackList
{
    protected $vendorPacksList;
    protected $composer;
    protected $root = '/';

    public function __construct(string $root, \Composer\Autoload\ClassLoader $composer = null)
    {
        if($composer === null) {
            $composer = "{$root}vendor/autoload.php";

            if(!file_exists($composer)) {
                throw new \Exception("Couldn't get composer");
            }

            $composer = require $composer;
        }

        $this->composer = $composer;

        $this->root = $root;

        $this->vendorPacksList = $this->getComposerPacks();
    }

    public function getVendorPath(string $pack): string
    {
        if(!empty($this->vendorPacksList[$pack])) {
            return $this->vendorPacksList[$pack];
        }

        return '';
    }

    protected function getComposerPacks(): array
    {
        $prefixes = array_merge($this->composer->getPrefixesPsr4(), $this->composer->getPrefixes());

        $packs = [];

        foreach ($prefixes as $key => $item) {
            if(strpos($key, 'Pack') !== false) {
                // Remove the root path and then composer relative path part
                // (e.g. C:\apache\htdocs\project\vendor/composer/../ExpPack => vendor/composer/../ExpPack => vendor/ExpPack)
                $pack = str_replace('composer/../', '', str_replace($this->root, '', $item[0]));

                $packs[substr($key, 0, -1)] = $pack;
            }
        }

        return $packs;
    }
}
