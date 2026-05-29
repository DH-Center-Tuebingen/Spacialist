<?php
namespace App\Services\Plugin;

use App\Plugin;
use App\Models\Plugin\Route as RouteModel;
use App\Support\BootstrapCache;
use Illuminate\Support\Facades\Schema;
use App\Plugin\PluginDirectory;
use App\Plugin\PluginManifest;
use App\Support\Log\PluginLog;
use Exception;
use Illuminate\Support\Facades\Route;

/**
 * Specifies routes defined by the plugin. The routes file will be loaded and registered with the application.
 * 
 * WARNING: When no route is specified in the manifest, the deprecated routes at 'routes/api.php' will be evaluated, this will be removed in a future version.
 * 
 * ```xml
 * <routes src="/lib/App/routes.php" middleware="api" />
 * ```
 */
class RouteService extends PluginService {

    use BootstrapCache;

    protected function getCacheName(): string {
        return 'plugin-routes';
    }

    public function fetch(): array {
        return RouteModel::all()->toArray();
    }

    public function install(Plugin $plugin, PluginManifest $manifest): void {
        $pluginDirectory = PluginDirectory::fromPlugin($plugin);
        $routesNode = $manifest->getTagNodes('routes');
        $middleware = 'api';
        if(empty($routes)) {
            $src = $this->findDeprecatedDefaultRoutes($plugin, $pluginDirectory);
        } else {
            $node = $routesNode[0];
            if(!isset($node['attributes']) || !isset($node['attributes']["src"])) {
                PluginLog::for($plugin)->warning("Routes tag found but 'src' attribute is missing. Please ensure your plugin manifest is correctly formatted.");
                return;
            }
            $srcFile = $node['attributes']["src"];
            $src = $pluginDirectory->getAbsolutePluginPath($srcFile);
            $middleware = $node['attributes']["middleware"] ?? 'api';
        }
        
        // Plugins does not need a routes file.
        if($src === null) {
            return;
        }

        if(!file_exists($src)) {
            PluginLog::for($plugin)->warning("Routes file not found at path: {$src}. Please ensure the 'src' attribute in your plugin manifest points to a valid file.");
            return;
        }

        RouteModel::create([
            'plugin_id' => $plugin->id,
            'src' => $src,
            'plugin_name' => $plugin->name,
            'plugin_slug' => $plugin->slugName(),
            'middleware' => $middleware,
        ]);
    }

    public function onAfterInstall(Plugin $plugin, PluginManifest $manifest): void {
        $this->cache();
    }

    public function findDeprecatedDefaultRoutes(Plugin $plugin, PluginDirectory $pluginDirectory): ?string {
        $pluginDir = PluginDirectory::fromPlugin($plugin);
        $filePath = $pluginDir->getAbsolutePluginPath('routes/api.php');

        if(file_exists($filePath)) {
            PluginLog::for($plugin)->warning("Deprecated default routes found. Please update your plugin to use the new route registration method.");
            return $filePath;
        } else {
            return null;
        }
    }

    public function uninstall(Plugin $plugin, PluginManifest $manifest): void {
        RouteModel::where('plugin_id', $plugin->id)->delete();
    }

    public function onAfterUninstall(Plugin $plugin, PluginManifest $manifest): void {
        $this->cache();
    }

    public function update(Plugin $plugin, PluginManifest $manifest): void {
        $this->uninstall($plugin, $manifest);
        $this->install($plugin, $manifest);
    }

    public function mapRoutes() {
        if(!Schema::hasTable('plugins'))
            return;

        $pluginRoutes = $this->getData();
        foreach($pluginRoutes as $route) {
            try {
                $prefix = "api/v1/{$route['plugin_slug']}";
                $namespace = "App\\Plugins\\{$route['plugin_name']}\\Controllers";
                $routesPath = $route['src'];
                $api = $route['middleware'] ?? 'api';

                if(file_exists($routesPath)) {                
                    Route::prefix($prefix)
                        ->middleware($api)
                        ->namespace($namespace)
                        ->group($routesPath);
                } else {
                    throw new Exception("Routes file not found for plugin {$route['plugin_name']} at path: {$routesPath}");
                }
            } catch(Exception $e) {
                // Log the error or handle it as needed
                PluginLog::logWarning("Failed to load routes for plugin {$route['plugin_name']}: " . $e->getMessage());
            }
        }
    }
}
