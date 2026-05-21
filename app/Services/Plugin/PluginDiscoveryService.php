<?php

namespace App\Services\Plugin;

use App\Plugin;
use App\Plugin\PluginDirectory;
use App\Plugin\PluginManifest;
use Illuminate\Support\Facades\File;

class PluginDiscoveryService extends PluginService {

    public function discover() {
        $availablePlugins = File::directories(PluginDirectory::getPath());
        $pluginNames = array_map(fn($path) => basename($path), $availablePlugins);
        self::discoverList($pluginNames);
    }

    public function discoverList(array $list): void {
        foreach($list as $pluginDirectory) {
            $this->discoverByName($pluginDirectory);
        }
    }

    public function discoverByName(string $name): ?Plugin {
        $manifest = PluginManifest::read($name);
        $plugin = null;
        if($manifest) {
            $plugin = Plugin::updateOrCreateFromManifest($manifest);
        }

        return $plugin;
    }
}