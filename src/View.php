<?php declare(strict_types=1);
namespace Prim;

use Closure;
use Exception;

class View implements ViewInterface
{
    /** @var Array<mixed> $options */
    protected array $options = [];

    protected string $templateName = 'design';
    protected string $templatePack = 'BasePack';
    protected string $pack = '';

    /** @var Array<mixed> $vars */
    protected array $vars = [];
    /** @var Array<string> $sections */
    protected array $sections = [];
    protected string $section = 'default';
    protected bool $sectionPush = false;

    /** @param Array<mixed> $options */
    public function __construct(protected PackList $packList, array $options = [])
    {
        $this->options = $options += [
            'root' => ''
        ];

        // Register view function shortcuts
        $this->registerFunction('e', function(?string $string) {
            return $this->escape($string);
        });
    }

    public function setPack(string $pack): void
    {
        $this->pack = $pack;
    }

    public function setTemplate(string $design, string $pack): void
    {
        $this->templateName = $design;
        $this->templatePack = $pack;
    }

    public function design(string $view, string $pack = '', array $vars = []): void
    {
        $this->renderTemplate($view, $pack, $vars, true, true);
    }

    /** @param Array<mixed> $vars */
    public function render(string $view, string $pack = '', array $vars = [], bool $template = true): void
    {
        $this->renderTemplate($view, $pack, $vars, $template, false);
    }

    public function escape(?string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE);
    }

    public function registerFunction(string $name, Closure $closure): void
    {
        if(!isset($this->vars[$name])) $this->vars[$name] = $closure;
    }

    public function registerGlobalVariable(string $name, mixed $var): void
    {
        if(!isset($this->vars[$name])) $this->vars[$name] = $var;
    }

    /**
     * @param Array<mixed> $vars
     * @return Array<mixed>
     */
    public function vars(array $vars = []): array
    {
        if(!empty($vars)) {
            $this->vars = $vars + $this->vars;
        }

        return $this->vars;
    }

    /** @param Array<mixed> $vars */
    public function renderTemplate(string $view, string $packDirectory = '', array $vars = [], bool $template = true, bool $default = false): void
    {
        $this->vars($vars);
        unset($vars);
        extract($this->vars);

        if($packDirectory == '') {
            $packDirectory = $this->pack;
        }

        $level = ob_get_level();

        try {
            if($default) $this->start('default');

            include($this->getViewFilePath($packDirectory, $view));

            if($default) $this->end();
        } catch (Exception $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }

            throw $e;
        }

        if ($template) {
            include($this->getViewFilePath($this->templatePack, "_templates/{$this->templateName}"));
        }
    }

    protected function getViewFilePath(string $pack, string $view): string
    {
        $localViewFile = "{$this->options['root']}src/$pack/view/$view.php";

        if(file_exists($localViewFile)) {
            return $localViewFile;
        }

        if($vendorPath = $this->packList->getVendorPath($pack)) {
            $vendorFile = "{$this->options['root']}$vendorPath/view/$view.php";

            if(file_exists($vendorFile)) {
                return $vendorFile;
            }
        }

        throw new Exception("Can't find view $view in $pack");
    }

    public function push(string $section): void
    {
        $this->start($section);
        $this->sectionPush = true;
    }

    public function start(string $section): void
    {
        $this->section = $section;
        ob_start();
    }

    public function end(): void
    {
        if($this->sectionPush) $this->sections[$this->section] .= ob_get_clean();
        else $this->sections[$this->section] = ob_get_clean();

        $this->sectionPush = false;
        $this->section = 'default';
    }

    public function section(string $section): string
    {
        return $this->sections[$section]?? '';
    }

    /** @param Array<mixed> $vars */
    public function fetch(string $name, string $pack = '', array $vars = []): string
    {
        $this->start('fetch');
        $this->renderTemplate($name, $pack, $vars, false, false);
        $this->end();

        return $this->section('fetch');
    }

    /** @param Array<mixed> $vars */
    public function insert(string $name, string $pack = '', array $vars = []): void
    {
        $this->renderTemplate($name, $pack, $vars, false, false);
    }

    public function addVar(string $name, mixed $var): void
    {
        $this->vars[$name] = $var;
    }

    public function addVars(array $vars): void
    {
        foreach($vars as $var) {
            $this->addVar($var[0], $var[1]);
        }
    }

    /** @return Array<mixed> */
    public function getVars(): array
    {
        return $this->vars;
    }

    public function fileHash(string $name): string
    {
        $path = $this->getFilePath($name);

        if(file_exists($path)) {
            $name .= '?v=' . hash_file('fnv1a32', $path);
        }

        return $name;
    }

    public function fileCache(string $name): string
    {
        $path = $this->getFilePath($name);

        if(file_exists($path)) {
            $name .= '?v=' . filemtime($path);
        }

        return $name;
    }

    public function getFilePath(string $name): string
    {
        return "{$this->options['root']}public/$name";
    }

    public function messageExist(): bool
    {
        return isset($_SESSION['_flashMessage']);
    }

    /** @return Array<mixed> */
    public function getMessage(bool $canBeDeleted = true): array
    {
        $message = $_SESSION['_flashMessage']?? [];

        if($canBeDeleted && $this->messageExist()) {
            unset($_SESSION['_flashMessage']);
        }

        return $message;
    }
}
