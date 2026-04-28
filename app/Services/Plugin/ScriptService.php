<?php

namespace App\Services\Plugin;

use App\File\Directory;
use App\Plugin;
use App\Plugin\PluginDirectory;
use App\Services\PluginManager;
use App\Support\Log\PluginLog;

class ScriptService extends PluginService {


    public function install(Plugin $plugin): void {
        $this->publish($plugin);
    }

    public function getUrl(Plugin $plugin): string {
        return "api/download/plugin/{$plugin->slugName()}-{$plugin->uuid}.js";
    }

    public function publish(Plugin $plugin): string {
        $pluginDirectory = PluginDirectory::byPlugin($plugin);
        $scriptPath = $pluginDirectory->getPluginPath("js/script.js");
        
        if(is_link($scriptPath)) {
            $scriptPath = readlink($scriptPath);
            if($scriptPath === false) {
                throw new \Exception("Could not read symlink for script file of plugin {$plugin->name}.");
            }
        }
        info("Publishing script for plugin {$plugin->name} from path: {$scriptPath}");
        
        if(file_exists($scriptPath)) {
            $filehandle = fopen($scriptPath, 'r');

            if(! $filehandle) {
                throw new \Exception("Could not open script file for plugin {$plugin->name}.");
            }

            $storageDirectory = new Directory("plugins", "private"); // Ensure the target directory exists
            $scriptName = $this->getScriptName($plugin);
            
            $scriptPath = $storageDirectory->getDirectoryPath($scriptName);
            // When the target is a symlink to the actual file, we just skip the publishing process.
            if(is_link($scriptPath)) {
                PluginLog::fromPlugin($plugin)->warning("Script for plugin {$plugin->name} is already published as a symlink. Skipping publishing process.");
                return $this->getUrl($plugin);
            }
            
            $storageDirectory->store(
                $scriptName,
                $filehandle
            );            
            fclose($filehandle);
            return $this->getUrl($plugin);
        } else {
            throw new \Exception("Script file for plugin {$plugin->name} does not exist at {$scriptPath}.");
        }
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
    
    public function downloadDirectory(): Directory {
        return new Directory('plugins', 'private');    
    }
}