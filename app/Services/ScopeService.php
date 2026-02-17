<?php

namespace App\Services;

use App\Plugin;
use Illuminate\Support\Facades\Cache;

class ScopeService extends CachedPluggableService
{

    protected function getCacheKey(): string
    {
        return 'plugin_scopes';
    }
    
    public function install(Plugin $plugin): void
    {
        $this->refreshCache();
    }
    
    public function update(Plugin $plugin): void
    {
        $this->refreshCache();
    }
    
    public function uninstall(Plugin $plugin): void
    {
        $this->refreshCache();
    }
    
    public function remove(Plugin $plugin): void
    {
        // No separate remove logic needed for hooks
        $this->refreshCache();
    }

    /**
     * Get all scopes defined in Plugins for a given model class (e.g. App\Entity).
     */
    public static function getScopesFor(string $modelClass)
    {
        $scopes = [];

        $installedPlugins = Plugin::getInstalled();
        foreach($installedPlugins as $plugin) {
            $pluginScopes = $plugin->getScopes();
            if(array_key_exists($modelClass, $pluginScopes)) {
                foreach($pluginScopes[$modelClass] as $scope) {
                    $scopes[] = $scope;
                }
            }
        }

        return $scopes;
    }

    public function refreshCache(): array
    {
        return $this->updateCache(function () {
            $info = self::getInfo();
            $scopes = [];
            if($info !== false) {
                if(array_key_exists('scopes', $info)) {
                    foreach($info['scopes'] as $scope) {
                        $attributes = $scope['@attributes'];
                        if(!array_key_exists('src', $attributes)) {
                            Log::error('<scope> attribute \'src\' is required');
                            continue;
                        }
                        if(!array_key_exists('on', $attributes)) {
                            Log::error('<scope> attribute \'on\' is required');
                            continue;
                        }

                        $src = $attributes['src'];
                        $on = $attributes['on'];

                        $srcDir = $this->getPath("Scopes");
                        if(!file_exists($srcDir) || !is_dir($srcDir)) {
                            Log::error('Missing \'Scopes\' directory');
                            continue;
                        }
                        $srcPath = $srcDir . DIRECTORY_SEPARATOR . $src;
                        if(!file_exists($srcPath)) {
                            Log::error("Missing file '$src'");
                            continue;
                        }
                        if(!class_exists($on)) {
                            Log::error("Class '{$on}' does not exist!");
                            continue;
                        }
                        $className = Str::replaceEnd('.php', '', $src);
                        $namespacedSrc = $this->getNamespace("\\Scopes\\$className");

                        if(!array_key_exists($on, $scopes)) {
                            $scopes[$on] = [];
                        }

                        $scopes[$on][] = $namespacedSrc;
                    }
                }
            }
            return $scopes;
        });
    }
}