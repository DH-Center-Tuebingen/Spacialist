<?php

namespace App\Services\Plugin;

use App\Interfaces\ManifestContent;
use App\Models\Plugin\AccessPoint;
use App\Plugin;
use App\Plugin\PluginManifest;
use App\Support\BootstrapCache;
use App\Support\Log\PluginLog;

/**
 * Allows the definition of custom access_points for the application, that require special
 * permission to visit specific routes of the webiste. 
 * 
 * '''xml
 * ...
 * <accesspoints>
 *      <accesspoint path="/path_to_access_point" id="ExamplePluginId" label="path.to.accesspoint.label" />
 * </accesspoints>
 * ...
 * '''
 */
class AccessPointsService extends PluginService implements ManifestContent {

    use BootstrapCache;

    protected function getCacheName(): string {
        return 'plugin-access-points';
    }

    public function fetch(): array {
        $accessPoints = AccessPoint::all()->toArray();
        $mappedAccessPoints = [];
        foreach($accessPoints as $accessPoint) {
            $mappedAccessPoints[$accessPoint['identifier']] = [
                'id' => $accessPoint['id'],
                'path' => $accessPoint['path'],
                'label' => $accessPoint['label'],
            ];
        }
        return $mappedAccessPoints;
    }

    public function install(Plugin $plugin, PluginManifest $manifest): void {
        $manifest = PluginManifest::fromPlugin($plugin);
        $accesPoints = $this->retrieveManifestValues($manifest);
        
        foreach($accesPoints as $key => $accessPoint) {
            PluginLog::for($plugin)->info("Installing access point with id '{$accessPoint['id']}' for plugin '{$plugin->name}'..." . json_encode($accessPoint));
            $this->installAccessPoint($accessPoint, $plugin, $manifest);
        }
    }

    private function installAccessPoint($accessPoint, Plugin $plugin, PluginManifest $manifest): bool {
        if(empty($accessPoint['id']) || empty($accessPoint['label']) || empty($accessPoint['path'])) {
            PluginLog::logWarning("Invalid access point definition in manifest for plugin {$plugin->name}: " . json_encode($accessPoint));
            return false;
        } else {

            if($this->accessPointIdAlreadyExists($accessPoint['id'])) {
                PluginLog::for($plugin)->warning("Access point with id '{$accessPoint['id']}' already exists. Skipping access point declaration in plugin manifest of {$manifest->getName()}.");
                return false;
            }
            AccessPoint::create([
                'plugin_id' => $plugin->id,
                'identifier' => $accessPoint['id'],
                'label' => $accessPoint['label'],
                'path' => $accessPoint['path'],
            ]);
            
            return true;
        }
    }

    private function accessPointIdAlreadyExists(string  $identifier) {
        return AccessPoint::where('identifier', $identifier)->exists();
    }

    public function update(Plugin $plugin, PluginManifest $manifest): void {
        $existingAccessPoints = AccessPoint::where('plugin_id', $plugin->id)->get();

        $addedAccessPoints = $this->retrieveManifestValues($manifest);

        for($existing = count($existingAccessPoints) - 1; $existing >= 0; $existing--) {
            $existingAccessPoint = $existingAccessPoints[$existing];
            for($added = count($addedAccessPoints) - 1; $added >= 0; $added--) {
                $addedAccessPoint = $addedAccessPoints[$added];
                if($existingAccessPoint->identifier === $addedAccessPoint['id']) {
                    // Access point already exists, so we remove it from the list of added access points and continue with the next existing access point.
                    unset($addedAccessPoints[$added]);
                    unset($existingAccessPoints[$existing]);
                    continue;
                }
            }
        }

        // The remaining existing access points are not present in the manifest anymore and have to be removed.
        foreach($existingAccessPoints as $existingAccessPoint) {
            $existingAccessPoint->delete();
        }

        // The remaining added access points are new and have to be created.
        foreach($addedAccessPoints as $addedAccessPoint) {
            $this->installAccessPoint($addedAccessPoint, $plugin, $manifest);
        }
    }

    public function uninstall(Plugin $plugin, PluginManifest $manifest): void {
        AccessPoint::where('plugin_id', $plugin->id)->delete();
    }

    public function retrieveManifestValues(PluginManifest $manifest): array {
        $accesspoints = [];
        $accessPointsXml = $manifest->getTagNodes("accesspoints/accesspoint");
        foreach($accessPointsXml as $key => $accesspoint) {
            $attributes = $accesspoint['attributes'] ?? [];
            $accesspoints[$key] = [
                "id" => $attributes['id'] ?? "",
                "label" => $attributes['label'] ?? "",
                "path" => $attributes['path'] ?? "",
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
}