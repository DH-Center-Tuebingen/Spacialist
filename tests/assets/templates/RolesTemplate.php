<?php

namespace Tests\Assets\Templates;

use Tests\Support\PluginTemplate;
use Tests\Support\RoleExtension;
use Tests\Support\RoleExtensionFile;


class RolesTemplate extends PluginTemplate {

    protected function __construct(protected ?string $name="RoleTemplate", protected ?string $key = "hook-plugin-message", protected ?string $version = "1.0.0", protected ?string $uuid = "123e4567-e89b-12d3-a456-426614174000", protected array $roleFiles = []) {
        return parent::__construct($name, $uuid, $version);
    }

    public function getAdditionalStructure() : array{
        $structure = [];

        foreach($this->roleFiles as $file) {
            $leaf = &$structure;
            $src = $file->src;
            $parts = explode("/", $src);
            if(count($parts) >= 1) {
                while(count($parts) > 1) {
                    $part = array_shift($parts);
                    if(!isset($leaf[$part])) {
                        $leaf[$part] = [];
                    }
                    $leaf = &$leaf[$part];
                }
                $leaf[array_shift($parts)] = $file->toJson();
            } else {
                info("File src '{$src}' does not contain a directory structure. Skipping.");
            }
        }

        return $structure;
    }

    public function getAdditionalInfoContent() : string {
        $rolesText = "<role-presets>\n";
        foreach($this->roleFiles as $file) {
            $rolesText .= "<role-preset src=\"{$file->src}\" />\n";
        }
        $rolesText .= "</role-presets>\n";
        return $rolesText;
    }

    public static function createFrom(?string $name = null,  ?string $version = null, ?string $uuid = null, array $roleFiles = []) : static {
        return new static(
            name: $name ?? 'RolePresetPlugin',
            version: $version ?? '1.0.0',
            uuid: $uuid ?? '123e4567-e89b-12d3-a456-426614174000',
            roleFiles: $roleFiles
        );
    }

    public static function create() : static {
        return static::createFrom();
    }

    public static function getSimpleRolesFile(string $src, ?string $name = null): RoleExtensionFile {
        return new RoleExtensionFile(
            src: $src,
            roleExtensions: [
                new RoleExtension('administrator', 'simple', 'crwds')
            ]
        );
    }

    public static function getComplexRolesFile(string $src, ?string $name = null): RoleExtensionFile {
        return new RoleExtensionFile(
            src: $src,
            roleExtensions: [
                new RoleExtension('administrator', 'complex', 'crwds'),
                new RoleExtension('guest', 'complex', 'r'),
            ]
        );
    }
}