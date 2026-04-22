<?php

namespace Tests\Assets\Templates;

use Tests\Support\PluginTemplate;
use Tests\Support\PluginTemplateFeatures\HookTemplateFeature;

class HookTemplate extends PluginTemplate {

    public function __construct(?string $name = null, ?string $uuid = null, ?string $version = null) {
        parent::__construct(
            $name ?? "HookPlugin",
            $uuid ?? "123e4567-e89b-12d3-a456-426614174001",
            $version ?? "1.0.0",
        );
    }

    public function getPredataFileContent(string $key, string $content): string {
        return <<<ADD_PRE_DATA
<?php
namespace App\Plugins\\{$this->plugin->name}\Hooks;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AddPreData {
    public static function apply(Request \$request, JsonResponse \$response): void {
        \$data = \$response->getData(true);
        \$data['$key'] = '$content';
        \$response->setData(\$data);
    }
}
ADD_PRE_DATA;
    }

    public function addPreHookFile(
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
            "Hooks/AddPreData.php",
            $this->getPredataFileContent($key, $content)
        );
        return $this;
    }


    public function addPreHook(): static {
        $hookFeature = new HookTemplateFeature();
        $hookFeature->addHook(
            on: 'api/v1/pre',
            src: "Hooks\\AddPreData@apply"
        );
        $this->addFeature($hookFeature);
        return $this;
    }

    public function addBasic(?string $key = null, ?string $content = null): static {
        parent::addBasic();
        
        return static::addPreHookFile($key, $content)->addPreHook();
    }

    public static function getBasic(?string $key = null, ?string $content = null): static {
        return (new static())->addBasic($key, $content)->generate();
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