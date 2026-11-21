<?php

namespace App\Http\Middleware;

use App\Preference;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOpenAccessEnabled {
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        if(!Preference::hasPublicAccess()) {
            abort(404, "Not Found");
        }

        return $next($request);
    }
}
