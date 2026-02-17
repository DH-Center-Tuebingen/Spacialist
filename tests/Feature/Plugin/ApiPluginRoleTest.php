<?php

namespace Tests\Feature;

use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

use Tests\Assets\Templates\RolesTemplate;
use Tests\Support\PluginGenerator;

class ApiPluginRoleTest extends TestCase {

    function testSimpleRole() {

        $generator = new PluginGenerator([
            RolesTemplate::createFrom(
                name: "RolePlugin",
                uuid: "123e4567-e89b-12d3-a456-426614174000",
                roleFiles: [
                    RolesTemplate::getSimpleRolesFile('role-presets.json')
                ]
            )
        ]);

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
        $generator = new PluginGenerator([
            RolesTemplate::createFrom(
                name: "RolePlugin",
                uuid: "123e4567-e89b-12d3-a456-426614174000",
                roleFiles: [
                    RolesTemplate::getComplexRolesFile('role-presets.json'),
                    RolesTemplate::getSimpleRolesFile('additional-role-presets.json')
                ]
            )
        ]);

        $generator->use(function () {
            $response = $this->userRequest()->get('/api/v1/pre');

            $this->assertStatus($response, 200);

            $content = $response->getContent();
            $json = json_decode($content, true);

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

    function testComplexRole() {
        $generator = new PluginGenerator([
            RolesTemplate::createFrom(
                name: "RolePlugin",
                uuid: "123e4567-e89b-12d3-a456-426614174000",
                roleFiles: [
                    RolesTemplate::getComplexRolesFile('role-presets.json')
                ]
            )
        ]);

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