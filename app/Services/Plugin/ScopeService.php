<?php

namespace App\Services\Plugin;

use App\Models\Plugin\Scopes;
use App\Plugin;
use App\Support\BootstrapCache;
use App\Support\Log\PluginLog;

use Illuminate\Support\Str;

class ScopeService extends PluginService {

    use BootstrapCache;

    protected function getCacheName(): string {
        return 'plugin_scopes';
    }

    public function install(Plugin $plugin): void {
        $scopes = $this->getScopesFor($plugin);
        $this->clearScopesOf($plugin);
        $this->createScopesFor($plugin, $scopes);
        $this->cache();
    }

    public function update(Plugin $plugin): void {
    }

    public function uninstall(Plugin $plugin): void {
    }

    public function remove(Plugin $plugin): void {
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

    protected function getScopesOf(Plugin $plugin): array {
        $info = $plugin->getInfo();
        $scopes = [];
        if($info !== false) {
            if(array_key_exists('scopes', $info)) {
                $pluginLogger = new PluginLog($plugin);
                foreach($info['scopes'] as $scope) {
                    if(isset($scope['@attributes'])) {


                        $attributes = $scope['@attributes'];
                        if(!array_key_exists('src', $attributes)) {
                            $pluginLogger->error('<scope> attribute \'src\' is required');
                            continue;
                        }
                        if(!array_key_exists('on', $attributes)) {
                            $pluginLogger->error('<scope> attribute \'on\' is required');
                            continue;
                        }

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
                        $namespacedSrc = $plugin->getNamespace("\\Scopes\\$className");

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