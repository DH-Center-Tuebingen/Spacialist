<?php

namespace App\Services;

use App\Support\Log\PluginLog;
use Exception;
use Carbon\Carbon;

use Illuminate\Support\Facades\DB;

use App\Exceptions\PluginLifecycleException;
use App\Plugin;
use App\Plugin\Directory as PluginDirectory;
use App\Plugin\Manifest as PluginManifest;
use App\Services\Plugin\AccessPointsService;
use App\Services\Plugin\AttributeService;
use App\Services\Plugin\CssService;
use App\Services\Plugin\DependencyService;
use App\Services\Plugin\DiscoveryService;
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

    /**
     * Every lifecycle event follows the same pattern.
     * This function unifies this pattern while allowing specific
     * actions after the (main) lifecycle event did complete.
     * @throws Exception - Propagades exceptions that are thrown inside any lifecycle event.
     * @param string $name - Name of the lifecycle event.
     * @param Plugin $plugin - Plugin being processed.
     * @param callable(Plugin, PluginManifest) $postAction - Action that should be executed after main function was called. Between main and onAfter call.
     * @return void
     */
    protected function wrapLifecycleEvent(string $name, Plugin $plugin, callable $postAction = null) {
        $manifest = PluginManifest::fromPlugin($plugin);
        $name = strtolower($name);
        $capitalName = ucfirst($name);

        try {
            DB::transaction(function () use ($plugin, $manifest, $name, $capitalName, $postAction) {

                foreach($this->pluggableServices as $service) {
                    call_user_func([$service, "onBefore$capitalName"], $plugin, $manifest);
                }

                foreach($this->pluggableServices as $service) {
                    call_user_func([$service, $name], $plugin, $manifest);
                }

                if(isset($postAction)) {
                    $postAction($plugin, $manifest);
                }

                foreach($this->pluggableServices as $service) {
                    call_user_func([$service, "onAfter$capitalName"], $plugin, $manifest);
                }
            });

        } catch(Exception $e) {
            foreach($this->pluggableServices as $service) {
                // The 'onFailed' hooks should not cancel the lifecycle operation,
                // as it would leave the application in an unpredictable state.
                // They are catched and errors are reported inside the plugin log.
                try {
                    call_user_func([$service, "on{$capitalName}Failed"], $plugin, $manifest);
                } catch(Exception $e) {
                    PluginLog::for($plugin)->error("Error while executing 'on{$capitalName}Failed' of " . get_class($service) . ": " . $e->getMessage());
                }
            }
            // We need to rebuild the cache even when the installation fails to avoid a bad state of the cache.
            $this->rebuildPluginCache();
            throw $e;
        }

        foreach($this->pluggableServices as $service) {
            // The 'onSuccess' hooks should not cancel the lifecycle operation,
            // as it would leave the application in an unpredictable state.
            // They are catched and errors are reported inside the plugin log.
            try {
                call_user_func([$service, "on{$capitalName}Success"], $plugin, $manifest);
            } catch(Exception $e) {
                PluginLog::for($plugin)->error("Error while executing 'on{$capitalName}Success' of " . get_class($service) . ": " . $e->getMessage());
            }
        }

        $this->rebuildPluginCache();
    }

    /**
     * Get's all plugins, optionally in a slim version without all the metadata.
     * @param bool $slim
     * @return Plugin[]
     */
    public function getPlugins(bool $slim = false) {
        $data = static::getData();

        return array_map(function ($p) use ($slim) {
            $plugin = new Plugin();
            $plugin->id = $p['id'];
            $plugin->name = $p['name'];
            $plugin->uuid = $p['uuid'];
            $plugin->installed_at = $p['installed_at'];

            if(!$slim) {
                $plugin->version = $p['version'];
                $plugin->update_available = $p['update_available'] ?? null;
                $plugin->updated_at = $p['updated_at'];
                $plugin->created_at = $p['created_at'];
                $plugin->metadata = $p['metadata'] ?? [];
            }
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
        $this->wrapLifecycleEvent("install", $plugin, function ($plugin) {
            $plugin->installed_at = Carbon::now();
            $plugin->save();
        });
    }

    /**
     * Updates a plugin.
     *
     * @param Plugin $plugin
     * @return string
     */
    public function update(Plugin $plugin): string {
        $oldVersion = $plugin->version;

        $this->wrapLifecycleEvent("update", $plugin, function ($plugin, $manifest) {
            $plugin->update_available = null;
            $plugin->version = $manifest->getVersion();
            $plugin->save();
        });

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
        $this->wrapLifecycleEvent("uninstall", $plugin, function ($plugin) {
            $plugin->installed_at = null;
            $plugin->save();
        });
    }

    public function remove(Plugin $plugin): void {

        if(isset($plugin->installed_at)) {
            throw new PluginLifecycleException($plugin, "Cannot remove installed plugin. Uninstall it first!");
        }
        $this->wrapLifecycleEvent("remove", $plugin, function($plugin){
            $plugin->delete();   
        });
        PluginDirectory::fromPlugin($plugin)->remove();
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

    public function clearPluginCache(): void {
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