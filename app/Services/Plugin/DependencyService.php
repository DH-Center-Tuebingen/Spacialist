<?php

namespace App\Services\Plugin;

use App\Exceptions\PluginLifecycleException;
use App\Globals;
use App\Models\Plugin\Dependencies;
use App\Plugin;
use App\Plugin\PluginManifest;
use App\Support\BootstrapCache;
use App\Support\Log\PluginLog;
use App\Support\Plugin\Dependency;


/**
 * Simple dependency requirements for the Plugin System.
 * Plugins can only be installed when the requirement is met.
 * 
 * ```xml
 * <dependencies>
 *      <core min='0.12.0'>
 *      <plugin>File</plugin>
 *      <plugin>Map</plugin>
 *      ...
 * </dependencies>
 * ```
 */
class DependencyService extends PluginService {

    use BootstrapCache;
    
    private array $dependencyErrors = [];
    private array $dependencies = [];
    
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
        [$error] = $this->evaluateDependencies($plugin, $manifest);
        if($this->dependencyErrors != ''){
            throw new PluginLifecycleException(
                $plugin, 
                __('Voraussetzungen für die Installation sind nicht erfüllt:\n:dependencyError', $dependencyErrors)
            );
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
                PluginLog::for($plugin)->warning('Invalid dependency declaration in plugin manifest of {$manifest->getName()}. Missing "tag" attribute.');
                continue;
            }
            
            // Allows users to type the plugin in lowercase, or
           
                   
            // $dependsOnPlugin = Plugin::where('name', $dependsOn)->first() ?? null;
            // if(!$dependsOnPlugin) {
            //     throw new Exception('Plugin dependency '$dependsOn' not found for plugin '{$manifest->getName()}'. This should have been caught in the onBeforeInstall check.');
            // }
        
            // Dependencies::create([
            //     'plugin_id' => $plugin->id,
            //     'depends_on' => $dependsOnPlugin->id,
            // ]);
        }
    }
    
    public function uninstall(Plugin $plugin, PluginManifest $manifest): void
    {
        parent::uninstall($plugin, $manifest);
    }
    
    private function getDependenciesFromManifest(PluginManifest $manifest): array {
        return $manifest->getTagNodes('dependencies/*');
    }
    
    
    /**
     * Checks if all dependencies are met.
     * 
     * @param Plugin\PluginManifest $manifest
     * @return array{ dependencies: Dependency[], errors: string[] } - Returns an array of the missing plugin names.
     */
    private function evaluateDependencies(Plugin $plugin, PluginManifest $manifest): array {
        $errors = [];
        $dependencies = $this->getDependenciesFromManifest($manifest);
        if(empty($dependencies)) {
            return [];
        }
    
        [
            'core' => $coreDependencies,
            'plugin' => $pluginDependencies,
            'unsupported' => $unsupportedDependencies,
        ] = $this->decomposeDependencies($dependencies);
        
        $this->logWarningOfUnsupportedDependencies($plugin, $unsupportedDependencies);
        $errors[] = $this->evaluatePluginDependencies($pluginDependencies);
        $errors[] = $this->evaluateCoreDependencies($coreDependencies);

        return [
            'dependencies' => $dependencies,
            'errors' => $errors,
        ];
    }
    
    /**
     * Sorts the dependencies into three differnt buckets: core, plugins and unsupported.
     */
    private function decomposeDependencies(array $dependencies) : array{
        $data = [
            'core' => [],
            'plugins' => [],
            'unsupported' => [],
        ];
        foreach($dependencies as $dependency) {
            switch($dependency['tag']) {
                case 'plugin': $data['plugins'][] = new Dependency($dependency); break;
                case 'core': $data['core'][] = new Dependency($dependency); break;
                default: $data['unsupported'][] = new Dependency($dependency);
            } 
        }
        
        return $data;
    }
    
    
    /**
     * Evaluates all unsupported dependencies and log them to the plugin log.
     * 
     * @param Plugin $plugin
     * @param Dependency[] $dependencies List of plugin dependencies.
     * @return void
     */
    private function logWarningOfUnsupportedDependencies(Plugin $plugin, array $dependencies): void {
        if(count($dependencies) == 0) return;
        $dependencyList = array_reduce($dependencies, function($carry, Dependency $dependency) {
            $carry[] = "[{$dependency->tag}]";
            return $carry;
        } , []);
        $dependencyText = implode(", ", $dependencyList);
        PluginLog::for($plugin)->warning("Dependencies are not supporter and ignored: $dependencyText");
    }
    
    /**
     * Evaluates if the plugin dependencies are met:
     * + Does the plugin exists
     * + Does the plugin meet the specified version range
     * 
     * @param Dependency[] $dependencies List of plugin dependencies.
     * @return string[] - Returns an array of errors messages encountered during evaliation.
     */
    private function evaluatePluginDependencies(array $dependencies): array{
        $errors = [];
        foreach($dependencies as $dependency) {
            $requiredPlugin = Plugin::where('name', $dependency->name)->first();
            if(!$requiredPlugin) {
                $errors[] = __("Required Plugin is missing ':pluginName'");
            } else if(!$requiredPlugin->installed_at) {
                $errors[] = __("Required Plugin is not installed ':pluginName'");
            } else {
                $dependency->supportsVersion($requiredPlugin->version);
            }
        }
        return $errors;
    }
    
    /**
     * Evaluates if the core dependencies are met:
     * + Is there only a single core dependency
     * + Is the core dependency in the version range.
     * 
     * @param Dependency[] $dependencies List of core dependencies.
     * @return string[] - Returns an array of errors messages encountered during evaliation.
     */
    private function evaluateCoreDependencies(array $dependencies): array {
        $errors = [];
        if(count($dependencies) > 0){
            $errors[] = __("Multiple core dependencies are present.");
        } else {
            $dependency = $dependencies[0];
            if(!$dependency->supportsVersion(Globals::getVersion()['release'])){
                $errors[] = __("Plugin is not compatible with the current Spacialist version.")
            }
        }
        return $errors;
    }
}