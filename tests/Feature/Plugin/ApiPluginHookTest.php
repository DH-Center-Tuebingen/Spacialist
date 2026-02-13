<?php

namespace Tests\Feature;

use Tests\Cases\Plugin\PluginHookTestCase;

use Tests\Assets\Templates\HookTemplate;
use Tests\Support\PluginGenerator;

class ApiPluginHookTest extends PluginHookTestCase {
    
    function testHookInteraction(){
        $response = $this->userRequest()->get('/api/v1/pre');
        
        $this->assertStatus($response, 200);
        
        $response->assertJson([
            'hook-plugin-message' => 'Executed'
        ]);
    }

    function testHookOrder(){
        $generator =new PluginGenerator([
            HookTemplate::createFrom(
                name: "HookOrderSecond",
                key: "hook-order",
                content: "Second",
                uuid: "123e4567-e89b-12d3-a456-426614174003",
                order: 2
            ),
            HookTemplate::createFrom(
                name: "HookOrderFirst",
                key: "hook-order",
                content: "First",
                uuid: "123e4567-e89b-12d3-a456-426614174002",
                order: 0
            )
        ]);

        $generator->setUp();

        $response = $this->userRequest()->get('/api/v1/pre');
        
        $this->assertStatus($response, 200);
        
        $response->assertJson([
            'hook-order' => 'First'
        ]);

        $generator->tearDown();
    }
}