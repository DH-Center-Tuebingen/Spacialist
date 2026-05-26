<?php

namespace App\Services\Plugin;

use App\Plugin;
use App\Plugin\PluginDirectory;
use App\Plugin\PluginManifest;
use Illuminate\Support\Facades\File;

class PluginDiscoveryService extends PluginService {

    /**
     * Finds the directories of the plugins in the plugin directory. This is done by looking for subdirectories in the plugin directory.
     * @return array<string> Full paths of the plugin directories.
     */
    public static function getPluginPaths(): array {
        return File::directories(PluginDirectory::getPath());
    }

    /**
     * Finds the names of the plugins in the plugin directory. This is done by looking for subdirectories in the plugin directory. 
     * @return array<string> Names of the plugins (which are the same as the names of the subdirectories in the plugin directory).
     */
    public static function getPluginNames(): array {
        $directories = self::getPluginPaths();
        return array_map(fn($path) => basename($path), $directories);
    }

    public function discover(): array {
        return $this->discoverList(self::getPluginNames());
    }

    public function discoverList(array $list): array {
        $plugins = [];
        foreach($list as $pluginDirectory) {
             $plugins[] = $this->discoverByName($pluginDirectory);
        }
        return $plugins;
    }

    public function discoverByName(string $name): ?Plugin {
        $manifest = PluginManifest::readFromName($name);
        $plugin = null;
        if($manifest) {
            $plugin = Plugin::updateOrCreateFromManifest($manifest);
        }

        return $plugin;
    }
}