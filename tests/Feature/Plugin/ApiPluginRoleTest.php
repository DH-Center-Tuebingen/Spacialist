<?php

namespace Tests\Feature;

use Illuminate\Testing\Fluent\AssertableJson;

use Tests\Support\PluginTemplate;
use Tests\Support\RoleExtension;
use Tests\TestCase;

use Tests\Support\PluginGenerator;
use Tests\Support\RoleExtensionFile;

class ApiPluginRoleTest extends TestCase {

    static function getDefaultTemplate() {
        return new PluginTemplate(
            name: "RolePlugin",
            uuid: "123e4567-e89b-12d3-a456-426614174000",
            version: "1.0.0"
        );
    }

    static function defaultRoleTemplate($filepath = 'roles.json') {
        $template = self::getDefaultTemplate();

        $extFile = new RoleExtensionFile(
            $filepath,
            [
                new RoleExtension("administrator", 'simple', 'crwds'),
                new RoleExtension("guest", 'simple', 'r')
            ]
        );

        return $template->addBasic()
            ->addFile(
                $extFile->src,
                $extFile->toJson(),
            )
            ->addXml("role-presets", "file", [
                ["src" => $extFile->src]
            ])
            ->generate("plugin.xml");
    }

    static function deprecatedRoleTemplate() {
        return static::defaultRoleTemplate('role-presets.json');
    }

    static function multipleFilesTemplate() {
        $template = self::getDefaultTemplate();

        $extFile1 = new RoleExtensionFile(
            "roles.json",
            [
                new RoleExtension("administrator", 'simple', 'crwds'),
                new RoleExtension("guest", 'simple', 'r')
            ]
        );

        $extFile2 = new RoleExtensionFile(
            "additional-roles.json",
            [
                new RoleExtension("administrator", 'complex', 'crwds'),
                new RoleExtension("guest", 'complex', 'r')
            ]
        );

        return $template->addBasic()
            ->addFile(
                $extFile1->src,
                $extFile1->toJson(),
            )
            ->addFile(
                $extFile2->src,
                $extFile2->toJson(),
            )
            ->addXml("role-presets", "file", [
                ["src" => $extFile1->src],
                ["src" => $extFile2->src]
            ])
            ->generate("plugin.xml");
    }

    function testSimpleRoleDefault() {
        $template = self::defaultRoleTemplate();
        $generator = new PluginGenerator([$template]);

        $generator->use(function () {
            $response = $this->userRequest()->get('/api/v1/pre');

            $this->assertStatus($response, 200);

            $response->assertJson(value: fn(AssertableJson $json) =>
                $json->has('presets')
                    ->where('presets.0.name', 'administrator')
                    ->has('presets.0.fullSet')
                    ->whereContains('presets.0.fullSet', [
                        'simple_create',
                        'simple_read',
                        'simple_write',
                        'simple_delete',
                        'simple_share',
                    ])
                    ->etc()
            );
        });
    }

    function testSimpleRoleDeprecated() {
        $template = self::multipleFilesTemplate();
        $generator = new PluginGenerator([$template]);

        $generator->use(function () {
            $response = $this->userRequest()->get('/api/v1/pre');

            $this->assertStatus($response, 200);

            $response->assertJson(value: fn(AssertableJson $json) =>
                $json->has('presets')
                    ->where('presets.0.name', 'administrator')
                    ->has('presets.0.fullSet')
                    ->whereContains('presets.0.fullSet', [
                        'simple_create',
                        'simple_read',
                        'simple_write',
                        'simple_delete',
                        'simple_share',
                    ])
                    ->etc()
            );
        }, true);
    }

    function testMultipleFiles() {
        $template = self::multipleFilesTemplate();
        $generator = new PluginGenerator([$template]);
        
        $generator->use(function () {
            $response = $this->userRequest()->get('/api/v1/pre');

            $this->assertStatus($response, 200);
            $response->assertJson(value: fn(AssertableJson $json) =>
                $json->has('presets')
                    ->where('presets.0.name', 'administrator')
                    ->has('presets.0.fullSet')
                    ->whereContains('presets.0.fullSet', [
                        'complex_create',
                        'complex_read',
                        'complex_write',
                        'complex_delete',
                        'complex_share',
                        'simple_create',
                        'simple_read',
                        'simple_write',
                        'simple_delete',
                        'simple_share',
                    ])
                    ->where('presets.1.name', 'guest')
                    ->has('presets.1.fullSet')
                    ->whereContains('presets.1.fullSet', [
                        'complex_read',
                    ])
                    ->etc()
            );
        }, true);
    }
}