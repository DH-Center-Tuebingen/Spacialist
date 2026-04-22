<?php

namespace App\Services\Plugin;

use App\File\Directory;
use App\Plugin;
use App\Plugin\PluginDirectory;
use App\Services\PluginManager;

class ScriptService extends PluginService {


    public function install(Plugin $plugin): void {
        $this->publish($plugin);
    }

    public function getUrl(Plugin $plugin): string {
        return "api/download/plugin/{$plugin->slugName()}-{$plugin->uuid}.js";
    }

    public function publish(Plugin $plugin): void {
        $pluginDirectory = PluginDirectory::byPlugin($plugin);
        $scriptPath = $pluginDirectory->getPluginPath("js/script.js");
        info("Publishing script for plugin {$plugin->name} from path: {$scriptPath}");
        
        if(is_link($scriptPath)) {
            $scriptPath = readlink($scriptPath);
        }
        info("Publishing script for plugin {$plugin->name} from path: {$scriptPath}");
        
        if(file_exists($scriptPath)) {
            $filehandle = fopen($scriptPath, 'r');

            if(! $filehandle) {
                throw new \Exception("Could not open script file for plugin {$plugin->name}.");
            }

            info("Publishing script for plugin {$plugin->name} from path: {$scriptPath}");
            $storageDirectory = new Directory("plugins", "private"); // Ensure the target directory exists
            $storageDirectory->store(
                $this->getScriptName($plugin),
                $filehandle
            );
            fclose($filehandle);
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