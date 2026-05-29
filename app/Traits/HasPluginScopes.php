<?php

namespace App\Traits;

use App\Services\Plugin\ScopeService;

trait HasPluginScopes {

    /**
     * Register plugin scopes for this model
     *
     * Note: This method is called automatically by Laravel when the model boots.
     */
    protected static function bootHasPluginScopes(): void {
        // The try-catch is to prevent issues during installation/package discovery
        try {
            $pluginScopes = app(ScopeService::class)->getScopesFor(static::class);
            foreach($pluginScopes as $pluginScope) {
                static::addGlobalScope(new $pluginScope);
            }
        } catch(\Exception $e) {
            // Fail silently during installation/package discovery
            return;
        }
    }
}