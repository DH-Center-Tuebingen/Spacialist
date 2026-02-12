<?php

namespace Tests\Feature;

use Tests\Cases\Plugin\PluginHookTestCase;

class ApiPluginHookTest extends PluginHookTestCase {
    
    function testHookInteraction(){
        $response = $this->userRequest()->get('/api/v1/pre');
        
        $this->assertStatus($response, 200);
        
        $response->assertJson([
            'hook-plugin-message' => 'Executed'
        ]);
    }
}