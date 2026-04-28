<?php

namespace App\Services\Plugin;

use App\Interfaces\ManifestContent;
use App\Models\Plugin\AccessPoint;
use App\Plugin;
use App\Plugin\PluginManifest;
use App\Support\BootstrapCache;
use App\Support\Log\PluginLog;

/**
 * Adds capability to publish custom CSS files to a plugin.
 * 
 * '''xml
 * ...
 * <accesspoint>
 *     <path>/path_to_access_point</path> <!-- full path e.g. https://spacialist.example.com/path_to_access_point -->
 *     <id>ExamplePluginId</id>
 *     <label>path.to.accesspoint.label</label>
 * </accesspoint>
 * ...
 * '''
 */
class AccessPointsService extends PluginService implements  ManifestContent {

    use BootstrapCache;

    protected function getCacheName(): string {
        return 'plugin_access_points';
    }
    
    public function install(Plugin $plugin): void {
        $manifest = PluginManifest::fromPlugin($plugin);
        $accesPoints = $this->retrieveManifestValues($manifest);
        
        foreach($accesPoints as $key => $accessPoint) {
            if(empty($accessPoint['id']) || empty($accessPoint['label']) || empty($accessPoint['path'])) {
                PluginLog::logWarning("Invalid access point definition in manifest for plugin {$plugin->name}: " . json_encode($accessPoint));
            } else {
                AccessPoint::create([
                    'plugin_id' => $plugin->id,
                    'identifier' => $accessPoint['id'],
                    'label' => $accessPoint['label'],
                    'path' => $accessPoint['path'],
                ]);
            }
        }
    }
    
    public function update(Plugin $plugin): void {
        $this->uninstall($plugin);
        $this->install($plugin);
    }
    
    public function uninstall(Plugin $plugin): void {
        AccessPoint::where('plugin_id', $plugin->id)->delete();
    }
        
    public function onAfterInstall(Plugin $plugin): void {
        $this->cache();
    }
    
    public function onAfterUninstall(Plugin $plugin): void {
        $this->cache();
    }
    
    public function onAfterUpdate(Plugin $plugin): void
    {
        $this->cache();
    }

    public function retrieveManifestValues(PluginManifest $manifest): array{
        $accesspoints = [];
        $manifestContent = $manifest->getContent();
        
        $accessPointsXml = $manifestContent['accesspoints']['accesspoint'] ?? [];        
        foreach($accessPointsXml as $key => $accesspoint) {        
            $accesspoints[$key] = [
                "id" => $accesspoint['id'] ?? "",
                "label" => $accesspoint['label'] ?? "",
                "path" => $accesspoint['path'] ?? "",
            ];
        }
    
        return $accesspoints;
    }
    
    public function verifyManifest(PluginManifest $manifest): bool {
        $accesspoints = $this->retrieveManifestValues($manifest);
        
        foreach($accesspoints as $key => $accesspoint) {
            if(empty($accesspoint['id']) || empty($accesspoint['label']) || empty($accesspoint['path'])) {
                return false;
            }
        }
        return true;
    }


    public function fetch(): array {
        return AccessPoint::all()->toArray();
    }
    
    public function createFromJson(Plugin $plugin, array $json): void {
        // Access points are not stored in the database, so we don't need to create any entries here.
        // The access points will be loaded from the plugin's manifest on each request.
    }
}