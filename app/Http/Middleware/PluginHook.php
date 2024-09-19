<?php

namespace App\Http\Middleware;

use App\Plugin;
use App\Plugin\HookRegister;
use Closure;
use Illuminate\Http\Request;

class PluginHook 
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array
     */
    
     public function handle(Request $request, Closure $next) {

         // PHP doesn't keep the state in cache between requests, so we need to reinitialize the plugin hooks.
         // REFACTOR:: This should be done in a more elegant way.
         Plugin::init();
      
         $actionNamespace = $request->route()->getActionName();
         $namespaceParts = explode('\\', $actionNamespace);
         $name = array_pop($namespaceParts);
         
         $response = $next($request);
         $response = HookRegister::get()->call($name, $request, $response);
    
         return $response;
     }
}
