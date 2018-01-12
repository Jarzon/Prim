<?php
namespace Prim;

class PackList
{
    protected $vendorPacksList;
    protected $composer;

    /**
     * @param \Composer\Autoload\ClassLoader $composer
     */
    public function __construct($composer)
    {
        $this->composer = $composer;
        $this->vendorPacksList = $this->getComposerPacks();
    }

    public function getVendorPath(string $pack)
    {
        if(!empty($this->vendorPacksList[$pack])) {
            return $this->vendorPacksList[$pack];
        }

        return '';
    }

    protected function getComposerPacks() {
        $prefixes = array_merge($this->composer->getPrefixesPsr4(), $this->composer->getPrefixes());

        $packs = [];

        foreach ($prefixes as $key => $item) {
            if(strpos($key, 'Pack') !== false) {
                // Remove the root path and then composer relative path part
                // (e.g. C:\apache\htdocs\project\vendor/composer/../ExpPack => vendor/composer/../ExpPack => vendor/ExpPack)
                $pack = str_replace('composer/../', '', str_replace(ROOT, '', $item[0]));

                $packs[substr($key, 0, -1)] = $pack;
            }
        }

        return $packs;
    }
}