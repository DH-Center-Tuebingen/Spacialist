<?php

namespace Tests\Feature;

use App\Services\HookService;
use Tests\TestCase;

use Tests\Assets\Templates\HookTemplate;
use Tests\Support\PluginGenerator;

class ApiPluginHookTest extends TestCase {

    
    function testHookInteraction(){

        PluginGenerator::with(
            [HookTemplate::getBasic()->generate()],
            function() {
                $response = $this->userRequest()->get('/api/v1/pre');
            
                $this->assertStatus($response, 200);
                
                $response->assertJson([
                    'hook-plugin-message' => 'Executed'
                ]);
        });
    }

    function testHookOrder(){
        $generator = new PluginGenerator([
            HookTemplate::createFrom(
                name: "HookOrderSecond",
                key: "hook-order",
                content: "Second",
                uuid: "123e4567-e89b-12d3-a456-426614174003",
            )->addPreDataHook(),
            HookTemplate::createFrom(
                name: "HookOrderFirst",
                key: "hook-order",
                content: "First",
                uuid: "123e4567-e89b-12d3-a456-426614174002",
            )->addPreDataHook()
        ]);

        $generator->use(function() {
            $response = $this->userRequest()->get('/api/v1/pre');
            
            $this->assertStatus($response, 200);
            
            $response->assertJson([
                'hook-order' => 'First'
            ]);
        });
    }
}