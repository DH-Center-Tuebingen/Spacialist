<?php

namespace App\Http\Middleware;

use App\Services\HookService;
use Closure;
use Illuminate\Http\Request;

class PluginHooks
{
    
    public function __construct(protected HookService $hookService){ }

    
     public function handle(Request $request, Closure $next) {
        $actionNamespace = $request->route()->getActionName();
        info("ActionName :: " . $actionNamespace);
        $namespaceParts = explode('\\', $actionNamespace);
        $name = array_pop($namespaceParts);
        
        $response = $next($request);
        // execute hooks with the response as first argument so hooks can modify it
        $response = $this->hookService->executeHooks($name, $response, $request);
    
        return $response;
     }
}