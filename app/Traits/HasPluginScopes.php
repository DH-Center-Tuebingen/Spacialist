<?php

namespace App\Traits;

use App\Plugin;

trait HasPluginScopes
{
    /**
     * Register plugin scopes for this model
     * 
     * Note: This method is called automatically by Laravel when the model boots.
     */
    protected static function bootHasPluginScopes(): void
    {
        $pluginScopes = Plugin::getScopesFor(static::class);
        info($pluginScopes);
        foreach($pluginScopes as $pluginScope) {
            static::addGlobalScope(new $pluginScope);
        }
    }
}