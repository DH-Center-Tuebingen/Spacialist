<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

use App\Plugin;
use App\Plugin\PluginDirectory;
use App\Plugin\PluginManifest;
use App\Services\Plugin\AccessPointsService;
use App\Services\Plugin\CssService;
use App\Services\Plugin\HookService;
use App\Services\Plugin\MigrationService;
use App\Services\Plugin\PermissionService;
use App\Services\Plugin\RolePresetService;
use App\Services\Plugin\RouteService;
use App\Services\Plugin\ScopeService;
use App\Services\Plugin\ScriptService;
use App\Support\BootstrapCache;

/**
 * A service class that manages all plugin related business logic,
 * such as installation, updates, and uninstallation of plugins.
 * 
 * It orchestrates the various features of the plugin system, 
 * such as hooks, migrations, permissions, ... .
 */

class PluginManager {
    use BootstrapCache;

    private array $pluggableServices = [];
    // private array $cachedServices = [];

    public function __construct(
        public readonly AccessPointsService $accessPoints,
        public readonly CssService $cssService,
        public readonly HookService $hookService,
        public readonly MigrationService $migrationService,
        public readonly PermissionService $permissionService,
        // public readonly RolePresetService $rolePresetService,
        public readonly RouteService $routeService,
        public readonly ScriptService $scriptService,
        public readonly ScopeService $scopeService,
    ) {
        $this->pluggableServices = func_get_args();
    }

    protected function getCacheName(): string {
        return 'plugins';
    }

    protected function fetch(): array {
        $dirs = File::directories(base_path('app/Plugins'));
        $cachedPlugins = [];
        foreach($dirs as $dir) {
            $manifest = PluginManifest::read($dir);
            if($manifest !== false) {
                $info = $manifest->getContent();

                if(!isset($info['name']) || !isset($info['version'])) {
                    continue;
                }

                $plugin = Plugin::updateOrCreateFromInfo($info);

                $cachedPluginInfo = [
                    'id' => $plugin->id,
                    'name' => $plugin->name,
                    'uuid' => $plugin->uuid,
                    'version' => $plugin->version,
                    'installed' => $plugin->installed_at ? $plugin->installed_at->toDateTimeString() : null,
                    'provider' => null,
                ];
                $cachedPlugins[] = $cachedPluginInfo;
            }
        }
        return $cachedPlugins;
    }

    public function getPlugins() {
        $data = static::getData();

        return array_map(function ($p) {
            $plugin = new Plugin();
            $plugin->id = $p['id'];
            $plugin->name = $p['name'];
            $plugin->uuid = $p['uuid'];
            $plugin->version = $p['version'];
            $plugin->installed_at = $p['installed'];
            return $plugin;
        }, $data);
    }

    /**
     * Retrieves all installed plugins preferably from the Bootstrap Cache.
     * If the cache is not set it will be built.
     * 
     * @return array
     */
    public function getInstalledPlugins(): array {
        return array_filter($this->getPlugins(), function ($p) {
            return $p['installed_at'] !== null;
        });
    }

    public function rebuildPluginCache() {
        // TODO::
    }

    //  public function rebuildPluginCache(){
    //     //Iterate over Plugin directory and cache all available plugins
    //     $dirs = File::directories(base_path('app/Plugins'));
    //     $cachedPlugins = [];
    //     foreach($dirs as $dir) {
    //         $info = Plugin::getPluginInfo($dir);
    //         if($info !== false) {

    //             if(!isset($info['name']) || !isset($info['version'])) {
    //                 continue;
    //             }

    //             $plugin = Plugin::updateOrCreateFromInfo($info);

    //             $cachedPluginInfo = [
    //                 'name' => $plugin->name,
    //                 'uuid' => $plugin->uuid,
    //                 'version' => $plugin->version,
    //                 'provider' => null,
    //             ];
    //             $cachedPlugins[] = $cachedPluginInfo;
    //         }
    //     }
    //     $cacheContent = "<?php\n\nreturn " . var_export($cachedPlugins, true) . ";\n";
    //     File::put(base_path('bootstrap/cache/plugins.php'), $cacheContent);
    //  }

    // public  function getCachedPlugins(){
    //     try{
    //         $plugins = require(base_path('bootstrap/cache/plugins.php'));
    //     }catch(\Exception $e){
    //         $this->rebuildPluginCache();
    //         $plugins = require(base_path('bootstrap/cache/plugins.php'));
    //     }
    //     return $plugins;
    //  }

    /**
     * Discovers and returns a list of all plugins.
     * 
     * @param bool $metadata
     * @return \Illuminate\Database\Eloquent\Collection<int, Plugin>
     */
    public function list(bool $metadata = false): Collection {
        self::discover();
        //TODO: Should be called from cache.
        $plugins = Plugin::all();

        if($metadata) {
            foreach($plugins as $plugin) {
                $plugin->metadata = $plugin->getMetadata();
                $plugin->changelog = $plugin->getChangelog();
            }
        }

        return $plugins;
    }

    public function cleanup(array $list): void {
        $pluginNames = [];

        foreach($list as $p) {
            $pluginNames[] = File::basename($p);
        }

        $nonExistingPlugins = Plugin::whereNotIn('name', $pluginNames)->get();
        foreach($nonExistingPlugins as $removedPlugin) {
            info("Plugin '{$removedPlugin->name}' does not exist anymore and will be removed from database.");
            $removedPlugin->handleRemove();
        }
    }

    /**
     * Traverses the plugin directory and finds all available plugins.
     * 
     * @return void
     */
    public function discover(): array {
        $availablePlugins = File::directories(PluginDirectory::getPath());
        self::discoverList($availablePlugins);
        self::cleanup($availablePlugins);
    }

    public static function discoverPluginByName($name): ?Plugin {
        $pluginPath = PluginDirectory::getPath($name);
        $info = self::getPluginInfo($pluginPath);
        if($info === FALSE) {
            return NULL;
        }

        $plugin = self::updateOrCreateFromInfo($info);
        return $plugin;
    }

    public function discoverList(array $list): void {
        foreach($list as $ap) {
            $info = self::getPluginInfo($ap);
            if($info !== FALSE) {
                self::updateOrCreateFromInfo($info);
            }
        }
    }

    public function install(Plugin $plugin): void {

        foreach($this->pluggableServices as $service) {
            $service->onBeforeInstall($plugin);
        }

        foreach($this->pluggableServices as $service) {
            $service->install($plugin);
        }

        $this->clearCache($plugin);
        $plugin->installed_at = Carbon::now();
        $plugin->save();

        foreach($this->pluggableServices as $service) {
            $service->onAfterInstall($plugin);
        }
    }

    public function update(Plugin $plugin): string {
        $oldVersion = $plugin->version;

        foreach($this->pluggableServices as $service) {
            $service->onBeforeUpdate($plugin);
        }

        foreach($this->pluggableServices as $service) {
            $service->update($plugin);
        }

        $info = $plugin->getInfo();
        $plugin->update_available = null;
        $plugin->version = $info['version'];
        $plugin->save();

        foreach($this->pluggableServices as $service) {
            $service->onAfterUpdate($plugin);
        }

        return $oldVersion;
    }

    public function uninstall(Plugin $plugin): void {
        foreach($this->pluggableServices as $service) {
            $service->onBeforeUninstall($plugin);
        }

        foreach($this->pluggableServices as $service) {
            $service->uninstall($plugin);
        }

        $this->clearCache($plugin);
        $plugin->installed_at = null;
        $plugin->save();

        foreach($this->pluggableServices as $service) {
            $service->onAfterUninstall($plugin);
        }
    }

    public function remove(Plugin $plugin): void {

        foreach($this->pluggableServices as $service) {
            $service->onBeforeRemove($plugin);
        }

        if(isset($plugin->installed_at)) {
            $this->uninstall($plugin);
        }

        foreach($this->pluggableServices as $service) {
            $service->remove($plugin);
        }

        $pluginDirectory = new PluginDirectory($plugin);
        sp_remove_dir($pluginDirectory->getPluginPath());
        $plugin->delete();

        foreach($this->pluggableServices as $service) {
            $service->onAfterRemove($plugin);
        }
    }

    public function clearCache(Plugin $plugin): void {
        foreach($this->pluggableServices as $service) {
            // The problem with traits is, that we cannot identify them easily
            // when they are implemented by the parent class. Therefore we use
            // the method_exists to check if the method supports caching.
            if(method_exists($service, 'clearCache')) {
                $service->clearCache($plugin);
            }
        }
    }

    // private function removePreferences(Plugin $plugin): void {
    //     $id = Str::kebab($plugin->name);
    //     Preference::where('label', 'ilike', "plugin.$id.%")->delete();
    // }
}