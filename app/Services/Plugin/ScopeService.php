<?php

namespace App\Services\Plugin;

use App\Exceptions\LifecycleOperation;
use App\Exceptions\PluginLifecycleException;
use App\Interfaces\ManifestContent;
use App\Models\Plugin\Scope;
use App\Plugin;
use App\Plugin\Directory as PluginDirectory;
use App\Plugin\Manifest as PluginManifest;
use App\Support\BootstrapCache;
use App\Support\Log\PluginLog;
use Illuminate\Support\Str;
use Psy\Readline\Hoa\Console;

/**
 * Plugin do support global Laravel Scopes.
 *
 * https://laravel.com/docs/13.x/eloquent#global-scopes
 *
 * ```xml
 * <scopes>
 *     <scope src="App\Plugins\ExamplePlugin\Scopes\ExampleScope" on="App\Entity" />
 *     <scope src="App\Plugins\ExamplePlugin\Scopes\AnotherScope" on="App\Entity" />
 * </scopes>
 * ```
 *
 */
class ScopeService extends PluginService implements ManifestContent {
    use BootstrapCache;

    protected function getCacheName(): string {
        return 'plugin-scopes';
    }

    public function install(Plugin $plugin, PluginManifest $manifest): void {
        if(!$this->verifyManifest($manifest)) {
            $pluginLogger = new PluginLog($plugin);
            $pluginLogger->error('Plugin manifest verification failed for ScopeService. Skipping scope registration.');
            throw new PluginLifecycleException($plugin, 'Plugin manifest verification failed for ScopeService. Check plugin logs for details.', LifecycleOperation::INSTALLATION);
        }

        $scopes = $this->retrieveManifestValues($manifest);
        $this->clearScopesOf($plugin);
        $this->createScopesFor($plugin, $scopes);
    }

    public function uninstall(Plugin $plugin, PluginManifest $pluginManifest): void {
        $this->clearScopesOf($plugin);
    }

    public function verifyManifest(PluginManifest $manifest): bool {
        $scopes = $manifest->getTagNodes('scopes/scope');
        $pluginLog = new PluginLog($manifest->getName());
        foreach($scopes as $key => $scope) {
            $attributes = $scope["attributes"] ?? [];
            if(!array_key_exists('src', $attributes)) {
                $pluginLog->error("Scope definition {#$key}  in manifest is missing required 'src' attribute.");
                return false;
            }
            if(!array_key_exists('on', $attributes)) {
                $pluginLog->error("Scope definition {#$key} in manifest is missing required 'on' attribute.");
                return false;
            }
        }
        return true;
    }

    protected function clearScopesOf(Plugin $plugin): void {
        Scope::where('plugin_id', $plugin->id)->delete();
    }

    protected function createScopesFor(Plugin $plugin, array $scopes): void {
        foreach($scopes as $on => $scopeClasses) {
            foreach($scopeClasses as $scopeClass) {
                Scope::create([
                    'plugin_id' => $plugin->id,
                    'namespace' => $scopeClass,
                    'on' => $on,
                ]);
            }
        }
    }

    public function retrieveManifestValues(PluginManifest $manifest): array {
        $scopes = [];
        $nodes = $manifest->getTagNodes('scopes/scope');
        foreach($nodes as $scope) {
            $attributes = $scope['attributes'] ?? [];
            $on = $attributes['on'];

            $namespaceSrc = str_replace("/", "\\", $attributes['src']);
            $namespaceSrc = Str::start($namespaceSrc, "\\");
            $namespacedSrc = PluginDirectory::namespaceOf($manifest->getName(), $namespaceSrc);

            if(!array_key_exists($on, $scopes)) {
                $scopes[$on] = [];
            }

            $scopes[$on][] = $namespacedSrc;
        }

        return $scopes;
    }

    /**
     * Get all scopes defined in Plugins for a given model class (e.g. App\Entity).
     */
    public function getScopesFor(string $modelClass) {
        $scopes = [];
        $pluginScopes = $this->getData(); // Ensure cache is loaded
        if(array_key_exists($modelClass, $pluginScopes)) {
            $scopes = $pluginScopes[$modelClass];
        }

        return $scopes;
    }

    public function fetch(): array {
        return Scope::all()->mapToGroups(function ($item) {
            return [$item->on => $item->namespace];
        })->toArray();
    }
}