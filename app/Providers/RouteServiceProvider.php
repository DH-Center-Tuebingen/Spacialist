<?php

namespace App\Providers;

use App\Plugin;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        $this->mapPluginRoutes();

        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
             ->middleware('api')
             ->namespace($this->namespace)
             ->group(base_path('routes/api.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapPluginRoutes()
    {
        if(!Schema::hasTable('plugins')) return;

        $installedPlugins = Plugin::whereNotNull('installed_at')->get();

        foreach($installedPlugins as $plugin) {
            info("Installing Plugin $plugin->name...");
            $snkName = Str::snake($plugin->name);
            $prefix = "api/v1/$snkName";
            $namespace = "App\\Plugins\\$plugin->name\\Controllers";
            $routesPath = "app/Plugins/$plugin->name/routes/api.php";

            Route::prefix($prefix)
                 ->middleware('api')
                 ->namespace($namespace)
                 ->group(base_path($routesPath));
        }
    }
}
