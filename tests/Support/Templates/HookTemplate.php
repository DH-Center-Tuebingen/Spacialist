<?php

namespace Tests\Support\Templates;

use Tests\Support\PluginTemplate;

class HookTemplate extends PluginTemplate {

    public function __construct(?string $name = null, ?string $uuid = null, ?string $version = null) {
        parent::__construct(
            $name ?? "HookPlugin",
            $uuid ?? "123e4567-e89b-12d3-a456-426614174001",
            $version ?? "1.0.0",
        );
    }

    public function getHookFileContent(string $key, string $content): string {
        return <<<ADD_PRE_DATA
<?php
namespace App\Plugins\\{$this->plugin->name}\Hooks;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VersionData {
    public static function addHookInfo(Request \$request, JsonResponse \$response): void {
        \$data = \$response->getData(true);
        \$data['$key'] = '$content';
        \$response->setData(\$data);
    }
        
    public static function addNameHook(Request \$request, JsonResponse \$response): void {
        \$data = \$response->getData(true);
        \$data['plugin_name'] = '{$this->plugin->name}';
        \$response->setData(\$data);
    }
        
    public static function addFooHook(Request \$request, JsonResponse \$response): void {
        \$data = \$response->getData(true);
        \$data['foo'] = 'bar';
        \$response->setData(\$data);
    }
        
    public function addBooHook(Request \$request, JsonResponse \$response): void {
        \$data = \$response->getData(true);
        \$data['boo'] = 'far';
        \$response->setData(\$data);
    }
    
}
ADD_PRE_DATA;
    }

    public function addHookFile(
        ?string $key = null,
        ?string $content = null
    ): static {
        if($key === null) {
            $key = "hook-plugin-message";
        }
        if($content === null) {
            $content = "Executed";
        }

        $this->addFile(
            "Hooks/VersionData.php",
            $this->getHookFileContent($key, $content)
        );
        return $this;
    }

    
    public function addHook(string $on, string $src, ?int $order = null): static {
        $this->addXml("hooks", "hook", [
            array_filter([
                "on" => $on,
                "src" => $src,
                "order" => $order
            ])
        ]);
        return $this;
    }

    public function addVersionHook(string $on = "api/v1/version", ?int $order = null): static {
        return $this->addHook(
            $on,
            "Hooks\\VersionData@addHookInfo",
            $order
        );
    }
    
    public function addNameHook(string $on = "api/v1/version", ?int $order = null): static {
        return $this->addHook(
            $on,
            "Hooks\\VersionData@addNameHook",
            $order
        );
    }
    
    public function addFooHook(string $on = "api/v1/version", ?int $order = null): static {
        return $this->addHook(
            $on,
            "Hooks\\VersionData@addFooHook",
            $order
        );
    }
    
    public function addBooHook(string $on = "api/v1/version", ?int $order = null): static {
        return $this->addHook(
            $on,
            "Hooks\\VersionData@addBooHook",
            $order
        );
    }

    /**
     * Adds the basic files to the template and additionally adds the default hook file
     * and xml configuration which targets the version endpoint and uses the addHookInfo method.
     * 
     * @param mixed $key
     * @param mixed $content
     * @param mixed $order
     * @return HookTemplate
     */
    public function addBasic(?string $key = null, ?string $content = null, ?int $order = null): static {
        parent::addBasic();
        return static::addHookFile($key, $content)->addVersionHook(order: $order);
    }

    public static function getBasic(?string $key = null, ?string $content = null, ?int $order = null): static {
        return (new static())->addBasic($key, $content, $order);
    }

    public static function createFrom(
        ?string $name,
        ?string $uuid,
        ?string $version,
    ): static {
        return (new static($name, $uuid, $version));
    }

    public static function generateHooksXML(array $hooks) {
        if($hooks === null || count($hooks) === 0) {
            return "";
        }
        $hooksXml = "";
        foreach($hooks as $hook) {
            $hooksXml .= "    <hook";
            if(isset($hook['on'])) {
                $hooksXml .= " on=\"{$hook['on']}\"";
            }
            if(isset($hook['src'])) {
                $hooksXml .= " src=\"{$hook['src']}\"";
            }
            if(isset($hook['order'])) {
                $hooksXml .= " order=\"{$hook['order']}\"";
            }
            $hooksXml .= " />\n";
        }
        return "<hooks>\n" . $hooksXml . "</hooks>";
    }

}