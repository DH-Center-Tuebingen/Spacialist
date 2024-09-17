<?php

namespace App\Plugin;

use App\Patterns\Singleton;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class HookRegister extends Singleton {
    
    private array $hooks = [];
    
    // Important: This must be redifined, otherwise PHP will use the 'last' child class that was loaded.
    protected static $instance = null;
   
    public function register(string $pluginName, Hook $hook, callable $callback): void {        
        if(!isset($this->hooks[$hook->value])) {
            $this->hooks[$hook->value] = [];
        }
        $this->hooks[$hook->value][] = ['plugin' => $pluginName, 'callback' => $callback];
    }
    
    public function call(string $method, Request $request, JsonResponse $response): JsonResponse {
        if(isset($this->hooks[$method])) {
            foreach($this->hooks[$method] as $data) {
                $pluginName = $data['plugin'];
                $callback = $data['callback'];
                try {
                    $callback($request, $response);
                } catch(\Exception $e) {
                    info("Error in plugin $pluginName: ".$e->getMessage());
                }
            }
        }
        
        return $response;
    }
}