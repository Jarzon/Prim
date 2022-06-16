<?php declare(strict_types=1);
namespace Prim;

use Composer\Autoload\ClassLoader;
use Exception;

class PackList
{
    protected array $vendorPacksList;
    protected ClassLoader $composer;
    protected string $root = '/';

    public function __construct(string $root, ClassLoader $composer = null)
    {
        if($composer === null) {
            $composer = "{$root}vendor/autoload.php";

            if(!file_exists($composer)) {
                throw new Exception("Couldn't get composer");
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
            if(str_contains($key, 'Pack')) {
                // Remove the root path and then composer relative path part
                // (e.g. C:\apache\htdocs\project\vendor/composer/../ExpPack => vendor/composer/../ExpPack => vendor/ExpPack)
                $pack = str_replace('composer/../', '', str_replace($this->root, '', $item[0]));

                $packs[substr($key, 0, -1)] = $pack;
            }
        }

        return $packs;
    }
}
