<?php

namespace App\Providers;

use App\Services\Plugin\CssService;
use App\Services\PluginManager;
use Illuminate\Support\ServiceProvider;

class PluginServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the PluginManager as a singleton
        // otherwise it may have multiple different instances
        // which causes issues when Services are stateful
        // e.g. setting disk on the CssService.
        $this->app->singleton(PluginManager::class);
    }
}
