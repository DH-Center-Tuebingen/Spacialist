<?php

namespace App\Services\Plugin;

use App\Exceptions\LifecycleOperation;
use App\Exceptions\PluginLifecycleException;
use App\Interfaces\ManifestContent;
use App\Models\Plugin\Scopes;
use App\Plugin;
use App\Plugin\PluginDirectory;
use App\Plugin\PluginManifest;
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
        Scopes::where('plugin_id', $plugin->id)->delete();
    }

    protected function createScopesFor(Plugin $plugin, array $scopes): void {
        foreach($scopes as $on => $scopeClasses) {
            foreach($scopeClasses as $scopeClass) {
                Scopes::create([
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


    protected function getScopesOf(Plugin $plugin): array {
        $info = $plugin->getInfo();
        $scopes = [];
        if($info !== false) {
            if(array_key_exists('scopes', $info)) {
                $pluginLogger = new PluginLog($plugin);
                foreach($info['scopes'] as $scope) {
                    if(isset($scope['@attributes'])) {


                        // VERIFY
                        // $attributes = $scope['@attributes'];
                        // if(!array_key_exists('src', $attributes)) {
                        //     $pluginLogger->error('<scope> attribute \'src\' is required');
                        //     continue;
                        // }
                        // if(!array_key_exists('on', $attributes)) {
                        //     $pluginLogger->error('<scope> attribute \'on\' is required');
                        //     continue;
                        // }

                        $src = $attributes['src'];
                        $on = $attributes['on'];

                        $srcDir = $plugin->getPath(path: "Scopes");
                        if(!file_exists($srcDir) || !is_dir($srcDir)) {
                            $pluginLogger->error('Missing \'Scopes\' directory');
                            continue;
                        }
                        $srcPath = $srcDir . DIRECTORY_SEPARATOR . $src;
                        if(!file_exists($srcPath)) {
                            $pluginLogger->error("Missing file '$src'");
                            continue;
                        }
                        if(!class_exists($on)) {
                            $pluginLogger->error("Class '{$on}' does not exist!");
                            continue;
                        }
                        $className = Str::replaceEnd('.php', '', $src);
                        $namespacedSrc = PluginDirectory::namespaceOf($plugin->name, "\\Scopes\\$className");

                        if(!array_key_exists($on, $scopes)) {
                            $scopes[$on] = [];
                        }

                        $scopes[$on][] = $namespacedSrc;
                    } else {
                        $pluginLogger->error('Scope entry is missing attributes. Skipping.');
                        continue;
                    }
                }
            }
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
        return Scopes::all()->mapToGroups(function ($item) {
            return [$item->on => $item->namespace];
        })->toArray();
    }
}