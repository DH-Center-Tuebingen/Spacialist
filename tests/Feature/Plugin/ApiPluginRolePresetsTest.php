<?php

namespace Tests\Feature\Plugin;

use Illuminate\Support\Collection;
use Illuminate\Testing\Fluent\AssertableJson;

use Tests\Support\PluginTemplate;
use Tests\Support\RoleExtension;
use Tests\TestCase;

use Tests\Support\PluginGenerator;
use Tests\Support\RoleExtensionFile;

class ApiPluginRolePresetsTest extends TestCase {
    
    protected string $filePath = 'custom/dir/roles.json';

    protected function getTemplateDefnition() {
        return new PluginTemplate(
            name: "RolePlugin",
            uuid: "123e4567-e89b-12d3-a456-426614174000",
            version: "1.0.0"
        );
    }
    
    protected function addManifestEntryToTemplate(PluginTemplate $template, array $files) {
        $template->addXml("role-presets", "file", array_map(fn($src) => ["src" => $src], $files));
    }

    protected function defaultRoleTemplate() {
        $template = $this->getTemplateDefnition();

        $extFile = new RoleExtensionFile(
            $this->filePath,
            [
                new RoleExtension("administrator", 'simple', 'crwds'),
                new RoleExtension("guest", 'simple', 'r')
            ]
        );
        
        $this->addManifestEntryToTemplate($template, [$this->filePath]);

        return $template->addBasic()
            ->addFile(
                $extFile->src,
                $extFile->toJson(),
            )
            ->generate("plugin.xml");
    }

    protected function multipleFilesTemplate() {
        $template = $this->getTemplateDefnition();

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
        
        $this->addManifestEntryToTemplate($template, [$extFile1->src, $extFile2->src]);

        return $template->addBasic()
            ->addFile(
                $extFile1->src,
                $extFile1->toJson(),
            )
            ->addFile(
                $extFile2->src,
                $extFile2->toJson(),
            )
            ->generate("plugin.xml");
    }

    function testSimpleRoles() {
        $template = $this->defaultRoleTemplate();
        PluginGenerator::with([$template], function () {
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

    function testMultipleFiles() {        
        $template = $this->multipleFilesTemplate();
        info($template);
        PluginGenerator::with([$template], function () {
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
                        'simple_read',
                    ])
                    ->etc()
            );
        });
    }

    function testRolesNotPresentWhenUninstalled() {
        PluginGenerator::with([$this->defaultRoleTemplate()->uninstalled()], function () {
            $response = $this->userRequest()->get('/api/v1/pre');

            $this->assertStatus($response, 200);
            $response->assertJson(value: fn(AssertableJson $json) =>
                $json->has('presets')
                    ->where('presets.0.name', 'administrator')
                    ->has('presets.0.fullSet')
                    ->where('presets.0.fullSet', function(Collection $fullSet){
                        $roles = [
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
                        ];
                        return !array_intersect($roles, $fullSet->toArray());
                    })
                    ->where('presets.1.name', 'guest')
                    ->has('presets.1.fullSet')
                    ->where('presets.1.fullSet', function(Collection $fullSet){
                        $roles = [
                            'complex_read',
                            'simple_read',
                        ];
                        return !array_intersect($roles, $fullSet->toArray());
                    })
                    ->etc()
            );
        });
    }

    function testRolesNotPresentWhenRemoved() {
        PluginGenerator::with([$this->defaultRoleTemplate()->removed()], function () {
            $response = $this->userRequest()->get('/api/v1/pre');

            $this->assertStatus($response, 200);
            $response->assertJson(value: fn(AssertableJson $json) =>
                $json->has('presets')
                    ->where('presets.0.name', 'administrator')
                    ->has('presets.0.fullSet')
                    ->where('presets.0.fullSet', function(Collection $fullSet){
                        $roles = [
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
                        ];
                        return !array_intersect($roles, $fullSet->toArray());
                    })
                    ->where('presets.1.name', 'guest')
                    ->has('presets.1.fullSet')
                    ->where('presets.1.fullSet', function(Collection $fullSet){
                        $roles = [
                            'complex_read',
                            'simple_read',
                        ];
                        return !array_intersect($roles, $fullSet->toArray());
                    })
                    ->etc()
            );
        });
    }
}