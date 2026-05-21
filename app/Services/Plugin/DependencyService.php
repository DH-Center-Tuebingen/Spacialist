<?php

namespace App\Services\Plugin;

use App\Exceptions\PluginLifecycleException;
use App\Models\Plugin\Dependencies;
use App\Plugin;
use App\Plugin\PluginManifest;
use App\Support\BootstrapCache;
use App\Support\Log\PluginLog;
use Exception;

/**
 * Simple dependency requirements for the Plugin System.
 * Plugins can only be installed when the requirement is met.
 * 
 * ```xml
 * <dependencies>
 *      <File />
 *      <Map />
 *      ...
 * </dependencies>
 * ```
 */
class DependencyService extends PluginService {

    use BootstrapCache;
    
    public function getCacheName(): string
    {
        return 'plugin-dependencies';
    }
    
    protected function fetch(): array
    {
        return Dependencies::all()->toArray();
    }
    
    public function onBeforeInstall(Plugin $plugin, PluginManifest $manifest): void
    {
        $missingDependencies = $this->missingDependencies($manifest);
        if(!empty($missingDependencies)) {
            $missingDependenciesList = implode(', ', $missingDependencies);
            throw new PluginLifecycleException($plugin, __("Cannot install plugin :pluginName because the following dependencies are missing: :missingDependencies.",[
                "pluginName" => $plugin->name,
                "missingDependencies" => $missingDependenciesList,
            ]));
        }
    }

    public function install(Plugin $plugin, PluginManifest $manifest): void
    {
        $dependencies = $this->getDependenciesFromManifest($manifest);
        if(empty($dependencies)) {
            return;
        }
        
        foreach($dependencies as $dependency) {
            $dependsOn = $dependency['tag'] ?? null;
            if(!$dependsOn) {
                PluginLog::for($plugin)->warning("Invalid dependency declaration in plugin manifest of {$manifest->getName()}. Missing 'tag' attribute.");
                continue;
            }
            
            $dependsOnPlugin = Plugin::where('name', $dependsOn)->first() ?? null;
            if(!$dependsOnPlugin) {
                throw new Exception("Plugin dependency '$dependsOn' not found for plugin '{$manifest->getName()}'. This should have been caught in the onBeforeInstall check.");
            }
        
            Dependencies::create([
                'plugin_id' => $plugin->id,
                'depends_on' => $dependsOnPlugin->id,
            ]);
        }
    }
    
    public function uninstall(Plugin $plugin, PluginManifest $manifest): void
    {
        parent::uninstall($plugin, $manifest);
    }
    
    private function getDependenciesFromManifest(PluginManifest $manifest): array {
        return $manifest->getTagNodes('dependencies/*');
    }
    
    private function missingDependencies(PluginManifest $manifest): array {
        $dependencies = $this->getDependenciesFromManifest($manifest);
        if(empty($dependencies)) {
            return [];
        }
        
        $missingDependencies = [];
        foreach($dependencies as $dependency) {
            
            if(!$dependency['tag'] || !is_string($dependency['tag'])) {
                PluginLog::for($plugin)->warning("Invalid dependency declaration in plugin manifest of {$manifest->getName()}. Missing or invalid 'tag' attribute.");
                continue;
            }
        
            $name = $dependency['tag'];
            $plugin = Plugin::where('name', $name)->first();
            if(!$plugin || !$plugin->installed_at) {
                $missingDependencies[] = $name;
            }
        }

        return $missingDependencies;
    }
}