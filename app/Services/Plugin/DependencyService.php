<?php

namespace App\Services\Plugin;

use App\Exceptions\PluginLifecycleException;
use App\Models\Plugin\Dependencies;
use App\Plugin;
use App\Plugin\PluginManifest;
use App\Support\BootstrapCache;
use App\Support\Log\PluginLog;
use App\Support\Plugin\Dependency;
use App\VersionInfo;


/**
 * Simple dependency requirements for the Plugin System.
 * Plugins can only be installed when the requirement is met.
 * 
 * ```xml
 * <dependencies>
 *      <core min='0.12.0'>
 *      <plugin name='File' min='1.0.0' max='2.0.0'/>
 *      <plugin name='Map' />
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
        ['errors' => $dependencyErrors] = $this->evaluateDependencies($plugin, $manifest);
        if(count($dependencyErrors) > 0){
            throw new PluginLifecycleException(
                $plugin, 
                __('Installation requirements are not met: :dependencyError', ['dependencyError' => implode(", ", $dependencyErrors)])
            );
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
            return [
                'dependencies' => [],
                'errors' => [],
            ];
        }
    
        [
            'core' => $coreDependencies,
            'plugins' => $pluginDependencies,
            'unsupported' => $unsupportedDependencies,
        ] = $this->decomposeDependencies($dependencies);
        
        $this->logWarningOfUnsupportedDependencies($plugin, $unsupportedDependencies);
        $errors = array_merge($errors, $this->evaluatePluginDependencies($pluginDependencies));   
        $errors = array_merge($errors, $this->evaluateCoreDependencies($coreDependencies));
        
        return [
            'dependencies' => $dependencies,
            'errors' => $errors,
        ];
    }
    
    /**
     * Sorts the dependencies into three differnt buckets: core, plugins and unsupported.
     * 
     * @param array<array<string, mixed>> $dependencies - The raw dependencies extracted from the manifest.
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
                $supported = $dependency->supportsVersion($requiredPlugin->version);
                if(!$supported){
                    $errors[] = __("Required Plugin ':pluginName' does not meet the version requirements.", ['pluginName' => $dependency->name]);
                }
            }
        }
        return $errors;
    }
    
    /**
     * Evaluates if the core dependencies are met:
     * + Is there only a single core dependency (or none at all)
     * + Is the core dependency in the version range.
     * 
     * @param Dependency[] $coreDependencies List of core dependencies.
     * @return string[] - Returns an array of errors messages encountered during evaliation.
     */
    private function evaluateCoreDependencies(array $coreDependencies): array {
        $errors = [];
        if(count($coreDependencies) > 1){
            $errors[] = __("Multiple core dependencies are present.");
        } else if(count($coreDependencies) === 1) {
            $dependency = $coreDependencies[0];
            $version = new VersionInfo();
            
            if(!$dependency->supportsVersion($version->getReleaseRaw())){
                $errors[] = __("Plugin is not compatible with the current Spacialist version.");
            }
        }
        return $errors;
    }
}