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

    public function __construct(public string $pluginName) {
    }
    
    
    /**
     * Get's the system path to the file relative to the provided path inside the plugin directory.
     * 
     * @param string $subpath - An optional subpath to a specific file or directory inside the plugin directory, e.g. "MyPlugin/Migrations" or "MyPlugin/Migrations/2024_01_01_000000_create_users_table.php"
     * @return string The absolute system path to the file relative to the provided path inside the plugin directory.
     */
    public function getAbsolutePluginPath(string $subpath = ""): string {
        return self::getPath($this->getPluginPath($subpath));
    }

    /**
     * Get's the path relative path inside the plugin directory, e.g. "MyPlugin" or "MyPlugin/Subdirectory".
     * 
     * @param string $subpath - An optional subpath to a specific file or directory inside the plugin directory, e.g. "Migrations" or "Migrations/2024_01_01_000000_create_users_table.php"
     * @return string The path to the plugin's directory relative to the plugin directory, e.g. "MyPlugin" or "MyPlugin/Subdirectory"
     */
    public function getPluginPath(string $subpath = ""): string {
        $path = $this->pluginName;
        if($subpath !== "") {
            $path .= Str::start($subpath, '/');
        }
        return $path;
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

    public static function fromPlugin(Plugin $plugin): self {
        return new self($plugin->name);
    }

    public static function getPathByName(string $pluginName): string {
        return self::getPath($pluginName);
    }

    public function remove() {
        sp_remove_dir($this->getAbsolutePluginPath());
    }

    public function readChangelog(?string $since = null): string {
        $changelog = $this->getAbsolutePluginPath('CHANGELOG.md');
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

    /**
     * Get the namespace for a given path within the plugin. If no path is provided, 
     * returns the base namespace for the plugin.
     * 
     * @param string|null $pluginName Optional path within the plugin to get the namespace for, separated 
     * by backslashes or forward slashes. For example, "Controllers/MyController.php" or 
     * "Controllers\MyController.php". Can start with or without a leading slash.
     */
    public static function namespaceOf(string $pluginName, $path = null): string {
        $pluginDirectory = new self($pluginName); // Validate plugin name
        return $pluginDirectory->getNamespace($path);    
    }

    public function getNamespace($path = null): string {
        $basePath = "App\\Plugins\\$this->pluginName";
        if($path) {
            $fixedPath = str_replace('/', '\\', $path);
            $basePath .= Str::start($fixedPath, '\\');
        }
        return $basePath;
    }
}