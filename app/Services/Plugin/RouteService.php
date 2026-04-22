<?php
namespace App\Services\Plugin;

use App\Plugin;
use App\Support\BootstrapCache;
use Illuminate\Support\Facades\Schema;
use App\Plugin\PluginDirectory;
use App\Services\PluginManager;
use Illuminate\Support\Facades\Route;

class RouteService extends PluginService {

    use BootstrapCache;

    protected function getCacheName(): string {
        return 'plugin-hooks';
    }

    public function fetch(): array {
        $routes = [];
        $installedPlugins = app(PluginManager::class)->getInstalledPlugins();
        foreach($installedPlugins as $plugin) {
            $routes = array_merge($routes, $plugin->getRoutes());
        }
        return $routes;
    }

    public function mapRoutes() {
        if(!Schema::hasTable('plugins'))
            return;

        $installedPlugins = Plugin::whereNotNull('installed_at')->get();

        foreach($installedPlugins as $plugin) {
            $slug = $plugin->slugName();
            $prefix = "api/v1/$slug";
            $namespace = "App\\Plugins\\$plugin->name\\Controllers";
            $routesPath = PluginDirectory::getPath($plugin->name . "/routes/api.php");

            if(file_exists($routesPath)) {
                Route::prefix($prefix)
                    ->middleware('api')
                    ->namespace($namespace)
                    ->group($routesPath);
            }
        }
    }
}
