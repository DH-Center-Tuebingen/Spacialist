<?php

namespace App\Http\Middleware;

use App\Services\Plugin\HookService;
use Closure;
use Illuminate\Http\Request;

class PluginHooks
{
    
    public function __construct(protected HookService $hookService){ }
    
     public function handle(Request $request, Closure $next) {
        $routeName = $request->route()->uri();
        $response = $next($request);
        // execute hooks with the response as first argument so hooks can modify it
        $response = $this->hookService->executeHooks($routeName, $request, $response);
        return $response;
     }


}