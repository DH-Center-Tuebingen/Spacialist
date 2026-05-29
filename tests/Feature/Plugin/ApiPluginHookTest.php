<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Support\Templates\HookTemplate;
use Tests\Support\PluginGenerator;

class ApiPluginHookTest extends TestCase {


    function testHookInteraction() {
        PluginGenerator::with(
            [HookTemplate::getBasic()->generate()],
            function () {
                $response = $this->userRequest()->get('/api/v1/version');

                $this->assertStatus($response, 200);

                $response->assertJson([
                    'hook-plugin-message' => 'Executed'
                ]);
            });
    }

    function testMultipleHooks() {
        PluginGenerator::with(
            [
                HookTemplate::getBasic()
                ->addFooHook()
                ->addBooHook()
                ->generate(),
            ],
            function () {
                $response = $this->userRequest()->get('/api/v1/version');

                $this->assertStatus($response, 200);

                $response->assertJson([
                    'foo' => 'bar',
                    'boo' => 'far'
                ]);
            }, true);
    }

    function testHooksOnDifferentEndpoints() {
        PluginGenerator::with(
            [
                HookTemplate::getBasic()
                    ->addBooHook(on: "api/v1/version")
                    ->addFooHook(on: "api/v1/pre")
                    ->generate(),
            ],
            function () {
                $boo = $this->userRequest()->get('/api/v1/version');
                $foo = $this->userRequest()->get('/api/v1/pre');

                $this->assertStatus($boo, 200);
                $this->assertStatus($foo, 200);

                $boo->assertJson([
                    'boo' => 'far'
                ]);

                $foo->assertJson([
                    'foo' => 'bar'
                ]);
            });
    }

    // Hooks should be executed in the order they are defined
    // meaning first inserted are executed first.
    function testHookDefaultOrder() {
        $generator = new PluginGenerator([
            HookTemplate::createFrom(
                name: "HookOrderFirst",
                uuid: "123e4567-e89b-12d3-a456-426614174003",
                version: "1.0.0"
            )
                ->addBasic('hook-order', 'HookOrderFirst')
                ->generate(),

            HookTemplate::createFrom(
                name: "HookOrderSecond",
                uuid: "123e4567-e89b-12d3-a456-426614174002",
                version: "1.0.0"
            )
                ->addBasic('hook-order', 'HookOrderSecond')
                ->generate()
        ]);

        $generator->use(function () {
            $response = $this->userRequest()->get('/api/v1/version');

            $this->assertStatus($response, 200);

            $response->assertJson([
                'hook-order' => 'HookOrderSecond'
            ]);
        });
    }

    function testHookCustomOrder() {
        $generator = new PluginGenerator([
            HookTemplate::createFrom(
                name: "HookOrderFirst",
                uuid: "123e4567-e89b-12d3-a456-426614174003",
                version: "1.0.0"
            )
                ->addBasic('hook-order', 'HookOrderFirst', order: 2)
                ->generate(),

            HookTemplate::createFrom(
                name: "HookOrderSecond",
                uuid: "123e4567-e89b-12d3-a456-426614174002",
                version: "1.0.0"
            )
                ->addBasic('hook-order', 'HookOrderSecond', order: 1)
                ->generate()
        ]);

        $generator->use(function () {
            $response = $this->userRequest()->get('/api/v1/version');

            $this->assertStatus($response, 200);

            $response->assertJson([
                'hook-order' => 'HookOrderFirst'
            ]);
        });
    }
}