<?php

namespace App\Services\Plugin;

use App\File\Directory;
use App\Plugin;
use App\Plugin\PluginDirectory;
use App\Plugin\PluginManifest;
use App\Services\PluginManager;
use App\Support\Log\PluginLog;


/**
 * Every Plugin requires to provide a .js file at 'js/script.js' 
 * this file will be copied to the /storage/app/private/plugins'
 * directory and is loaded by default by the application.
 */
class ScriptService extends PluginService {

    public const PLUGIN_SCRIPT_LOCATION = "js/script.js";

    public function install(Plugin $plugin, PluginManifest $manifest): void {
        $this->publish($plugin);
    }

    public function getUrl(Plugin $plugin): string {
        return "api/download/plugin/{$plugin->slugName()}-{$plugin->uuid}.js";
    }

    /**
     * The script path may be a symbolic link, esp. when used in production.
     * Therefore we need to check of the file exists or if the file is a symbolic
     * link, if the linked file does exist.
     * 
     * @param Plugin $plugin
     * @throws \Exception
     * @return bool|null
     */
    public function resolveScriptPath(Plugin $plugin, int $linkDepth = 3): ?string {
        $pluginDirectory = PluginDirectory::fromPlugin($plugin);
        $scriptPath = $pluginDirectory->getAbsolutePluginPath(self::PLUGIN_SCRIPT_LOCATION);

        if(!file_exists($scriptPath)) {
            return null;
        }

        $currentLinkDepth = 0;
        while(is_link($scriptPath)) {
            if($currentLinkDepth >= $linkDepth) {
                throw new \Exception("Maximum link depth of {$linkDepth} exceeded while resolving script path for plugin {$plugin->name}. Possible circular link detected.");
            }
            $scriptPath = readlink($scriptPath);
            if($scriptPath === false) {
                throw new \Exception("Could not read symlink for script file of plugin {$plugin->name}.");
            }
            $currentLinkDepth++;
        }

        return file_exists($scriptPath) ? $scriptPath : null;
    }

    /**
     * Publishes the script from the plugin directory to the storage directory
     * using the name schema {plugin-name}-{uuid}.js (plugin-name is slugified).
     * 
     * If the source file is a symbolic link the program tries to resolve it using 3
     * link levels. If the file is not found after resolving the links, an Exception is thrown. 
     * 
     * When the target file already exists as a symbolic link, it is managed by the maintainer
     * and the script should not overwrite it. A warning is logged to the PluginLog. 
     * 
     * 
     * @param Plugin $plugin
     * @throws \Exception - Throws an Exception when the source file is not present or cannot be read.
     * @return string - Returns the URL to the published script file.
     */
    public function publish(Plugin $plugin): string {

        // We start detecting the target file first, to return early, when the target file
        // is using a symlink.
        $storageDirectory = new Directory("plugins", "private");
        $scriptName = $this->getScriptName($plugin);
        $targetPath = $storageDirectory->getDirectoryPath($scriptName);

        if(is_link($targetPath)) { // we assume that the symlink is valid and skip the publishing process
            PluginLog::for($plugin)->warning("Script for plugin {$plugin->name} is already published as a symlink. Skipping publishing process.");
        } else { // when the target file is no symlink, we need to resolve the source file and copy it to the target location.
            $srcPath = $this->resolveScriptPath($plugin, 3);

            if(!isset($srcPath)) {
                throw new \Exception("Script file for plugin {$plugin->name} does not exist at {$srcPath}.");
            }

            $filehandle = fopen($srcPath, 'r');
            if(!$filehandle) {
                throw new \Exception("Could not open script file for plugin {$plugin->name}.");
            }

            $storageDirectory->store(
                $scriptName,
                $filehandle
            );
            fclose($filehandle);  
        }

        return $this->getUrl($plugin);
    }

    public function getScriptName(Plugin $plugin): string {
        return "{$plugin->slugName()}-{$plugin->uuid}.js";
    }

    public function getHtmlTags(): ?string {
        $scripts = "";
        $installedPlugins = app(PluginManager::class)->getInstalledPlugins();
        foreach($installedPlugins as $plugin) {
            $scriptUrl = $this->getUrl($plugin);
            $scripts .= "<script src=\"{$scriptUrl}\" defer></script>\n";
        }
        return $scripts;
    }

    public function getStorageDirectory(): Directory {
        return new Directory('plugins', 'private');
    }
}