<?php

namespace App\Http\Middleware;

use Closure;

class LanguageMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Set app locale to logged-in user's setting
        if(auth()->check()) {
            $lang = auth()->user()->getLanguage();
            \App::setLocale($lang);
        }
        return $next($request);
    }
}
