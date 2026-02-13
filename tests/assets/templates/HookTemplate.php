<?php

namespace Tests\Assets\Templates;

use Tests\Support\PluginTemplate;


class HookTemplate extends PluginTemplate {

    protected function __construct(protected ?string $name="HookPlugin", protected ?string $content="Executed", protected ?string $key = "hook-plugin-message", protected ?string $version = "1.0.0", protected ?string $uuid = "123e4567-e89b-12d3-a456-426614174000", protected ?int $order = 0)
    {
        return parent::__construct($name, $uuid, $version);
    }


    public function getAdditionalStructure() : array{
        return [
                'Hooks' => [
                    "AddPreData.php" => <<<ADD_PRE_DATA
<?php
namespace App\Plugins\\$this->name\Hooks;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AddPreData {
    public static function apply(Request \$request, JsonResponse \$response): void {
        \$data = \$response->getData(true);
        \$data['$this->key'] = '$this->content';
        \$response->setData(\$data);
    }
}
ADD_PRE_DATA,
                ]
            ];
    }
    public function getAdditionalInfoContent() : string {
        $hook ="<hooks>\n<hook on=\"api/v1/pre\" src=\"Hooks\\AddPreData@apply\"";
        if($this->order) {
            $hook .= " order=\"{$this->order}\"";
        }
        return $hook . " />\n</hooks>";
    }

    public static function createFrom(?string $name = null , ?string $content = null, ?string $key = null, ?string $version = null, ?string $uuid = null, ?int $order = 0) : static {
        return new static(
            name: $name ?? 'HookPlugin',
            content: $content ?? 'Executed',
            key: $key ?? 'hook-plugin-message',
            version: $version ?? '1.0.0',
            uuid: $uuid ?? '123e4567-e89b-12d3-a456-426614174000',
            order: $order ?? 0
        );
    }

    public static function create() : static {
        return static::createFrom();
    }
}