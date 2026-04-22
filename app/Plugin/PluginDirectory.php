<?php

namespace App\Plugin;

use App\Plugin;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Manages a plugin directory and its contents.
 * 
 * It concerns the plugin manifest (plugin.xml), components, options, classes, 
 * and other files that are part of a plugin.
 */
class PluginDirectory {

    public function __construct(public Plugin $plugin) {
    }

    /**
     * Get's the path to the plugin's directory relative to the plugin directory, e.g. "MyPlugin" or "MyPlugin/Subdirectory".
     * 
     * @param string $subpath - An optional subpath to a specific file or directory inside the plugin directory, e.g. "Migrations" or "Migrations/2024_01_01_000000_create_users_table.php"
     * @return string The path to the plugin's directory relative to the plugin directory, e.g. "MyPlugin" or "MyPlugin/Subdirectory"
     */
    public function getPluginPath(string $subpath = ""): string {
        $path = $this->plugin->name;
        if($subpath !== "") {
            $path .= Str::start($subpath, '/');
        }

        return self::getPath($path);
    }

    /**
     * Get's the absolute system path relative to the plugin directory. 
     * 
     * @param string $subpath - An optional subpath to a specific file or directory inside the plugin directory, e.g. "MyPlugin/Migrations" or "MyPlugin/Migrations/2024_01_01_000000_create_users_table.php"
     * @return string The absolute system path to the plugin's directory or a subpath inside the plugin's directory
     */
    public static function getPath(string $subpath = ""): string {
        $pluginDirectory = config('app.plugin_directory');
        $path = base_path($pluginDirectory);

        if($subpath !== "") {
            $path .= Str::start($subpath, '/');
        }

        return $path;
    }
    
    public static function byPlugin(Plugin $plugin): self {
        return new self($plugin);
    }

    public static function getPathByName(string $pluginName): string {
        return self::getPath($pluginName);
    }

    public static function getManifest($pluginName): PluginManifest|false {
        $path = self::getPath($pluginName);
        return PluginManifest::read($path);
    }

    public function readChangelog(): string {
        $changelog = $this->getPath('CHANGELOG.md');
        if(!File::isFile($changelog)) {
            return '';
        }

        $changes = file_get_contents($changelog);
        if(isset($since) && preg_match("/\\n#+\s(v\s?)?$since(\s-\s.+)?\\n/i", $changes, $matches, PREG_OFFSET_CAPTURE) !== FALSE) {
            if(count($matches) > 0) {
                $changes = substr($changes, 0, $matches[0][1]);
            }
        }

        return $changes;
    }
}