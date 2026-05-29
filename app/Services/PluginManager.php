<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;

use App\Plugin;
use App\Plugin\PluginDirectory;
use App\Plugin\PluginManifest;
use App\Services\Plugin\AccessPointsService;
use App\Services\Plugin\AttributeService;
use App\Services\Plugin\CssService;
use App\Services\Plugin\DependencyService;
use App\Services\Plugin\HookService;
use App\Services\Plugin\MigrationService;
use App\Services\Plugin\PermissionService;
use App\Services\Plugin\DiscoveryService;
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
 * 
 * To add a new feature to the plugin system, you can create a new PluginService
 * that implements the corresponding methods for installation, update, and uninstallation.
 */

class PluginManager {

    use BootstrapCache;

    private array $pluggableServices = [];

    public function __construct(
        public readonly AccessPointsService $accessPoints,
        public readonly AttributeService $attributeService,
        public readonly CssService $cssService,
        public readonly DependencyService $dependencyService,
        public readonly HookService $hookService,
        public readonly MigrationService $migrationService,
        public readonly PermissionService $permissionService,
        public readonly RolePresetService $rolePresetService,
        public readonly RouteService $routeService,
        public readonly ScriptService $scriptService,
        public readonly ScopeService $scopeService,
        public readonly DiscoveryService $discoveryService,
    ) {
        $this->pluggableServices = func_get_args();
    }

    protected function getCacheName(): string {
        return "plugins";
    }

    protected function fetch(): array {
        return Plugin::orderBy('id', 'asc')->get()->toArray();
    }

    public function cleanup(array $list): void {
        $pluginNames = [];

        foreach($list as $p) {
            $pluginNames[] = File::basename($p);
        }

        $nonExistingPlugins = Plugin::whereNotIn('name', $pluginNames)->get();
        foreach($nonExistingPlugins as $removedPlugin) {
            $removedPlugin->handleRemove();
        }
    }

    public function getPlugins() {
        $data = static::getData();

        return array_map(function ($p) {
            $plugin = new Plugin();
            $plugin->id = $p['id'];
            $plugin->name = $p['name'];
            $plugin->uuid = $p['uuid'];
            $plugin->version = $p['version'];
            $plugin->update_available = $p['update_available'] ?? null;
            $plugin->installed_at = $p['installed_at'];
            $plugin->updated_at = $p['updated_at'];
            $plugin->created_at = $p['created_at'];
            $plugin->metadata = $p['metadata'] ?? [];
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

    public function install(Plugin $plugin): void {

        $manifest = PluginManifest::fromPlugin($plugin);

        foreach($this->pluggableServices as $service) {
            $service->onBeforeInstall($plugin, $manifest);
        }

        foreach($this->pluggableServices as $service) {
            $service->install($plugin, $manifest);
        }

        $this->rebuildPluginCache();
        $plugin->installed_at = Carbon::now();
        $plugin->save();

        foreach($this->pluggableServices as $service) {
            $service->onAfterInstall($plugin, $manifest);
        }
    }

    /**
     * Updates a plugin.
     * 
     * @param Plugin $plugin
     * @return string
     */
    public function update(Plugin $plugin): string {
        $oldVersion = $plugin->version;
        $manifest = PluginManifest::fromPlugin($plugin);

        foreach($this->pluggableServices as $service) {
            $service->onBeforeUpdate($plugin, $manifest);
        }

        foreach($this->pluggableServices as $service) {
            $service->update($plugin, $manifest);
        }

        $this->rebuildPluginCache();
        $plugin->update_available = null;
        $plugin->version = $manifest->getVersion();
        $plugin->save();
        

        foreach($this->pluggableServices as $service) {
            $service->onAfterUpdate($plugin, $manifest);
        }

        return $oldVersion;
    }

    /**
     * Uninstalls a plugin. This will not remove the plugin, but removes all 
     * functionalities of the plugin from the database. 
     * 
     * @param Plugin $plugin
     * @param mixed $manifest - Manifest can be optionally provided to avoid reading the manifest multiple times during the uninstall process. 
     *                          If not provided, it will be read it from the plugin.
     * @return void
     */
    public function uninstall(Plugin $plugin, ?PluginManifest $manifest = null): void {
        if($manifest === null) {
            $manifest = PluginManifest::fromPlugin($plugin);
        }
        
        foreach($this->pluggableServices as $service) {
            $service->onBeforeUninstall($plugin, $manifest);
        }

        foreach($this->pluggableServices as $service) {
            $service->uninstall($plugin, $manifest);
        }

        $this->rebuildPluginCache();
        $plugin->installed_at = null;
        $plugin->save();

        foreach($this->pluggableServices as $service) {
            $service->onAfterUninstall($plugin, $manifest);
        }
    }

    public function remove(Plugin $plugin): void {
        $manifest = PluginManifest::fromPlugin($plugin);

        foreach($this->pluggableServices as $service) {
            $service->onBeforeRemove($plugin, $manifest);
        }

        if(isset($plugin->installed_at)) {
            $this->uninstall($plugin, $manifest);
        }

        foreach($this->pluggableServices as $service) {
            $service->remove($plugin, $manifest);
        }

        PluginDirectory::fromPlugin($plugin)->remove();
        $plugin->delete();

        foreach($this->pluggableServices as $service) {
            $service->onAfterRemove($plugin, $manifest);
        }
    }

    public function rebuildPluginCache() {
        $this->cache();
        foreach($this->pluggableServices as $service) {
            // The problem with traits is, that we cannot identify them easily
            // when they are implemented by the parent class. Therefore we use
            // the method_exists to check if the method supports caching.
            if(method_exists($service, 'cache')) {
                $service->cache();
            }
        }
    }
    
    public function clearCache(): void {
        $this->clearCache();
        foreach($this->pluggableServices as $service) {
            // The problem with traits is, that we cannot identify them easily
            // when they are implemented by the parent class. Therefore we use
            // the method_exists to check if the method supports caching.
            if(method_exists($service, 'clearCache')) {
                $service->clearCache();
            }
        }
    }
}