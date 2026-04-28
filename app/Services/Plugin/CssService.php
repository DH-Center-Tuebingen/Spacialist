<?php

namespace App\Services\Plugin;

use App\File\Directory;
use App\Interfaces\ManifestContent;
use App\Models\Plugin\CssFile;
use App\Plugin;
use App\Plugin\PluginDirectory;
use App\Plugin\PluginManifest;
use App\Services\PluginManager;
use App\Support\BootstrapCache;
use App\Support\Log\PluginLog;
use Illuminate\Support\Facades\DB;

/**
 * Adds capability to publish custom CSS files to a plugin.
 * 
 * '''xml
 * ...
 *    <css>
 *        <file name="file">path/to/file.css</file>
 *    </css>
 * ...
 * '''
 */
class CssService extends PluginService implements ManifestContent {

    use BootstrapCache;

    protected function fetch(): array {
        return CssFile::all()->toArray();
    }

    protected function getCacheName(): string {
        return "plugin-css.php";
    }

    public function install(Plugin $plugin): void {
        $this->updateOrInstall($plugin);
    }

    public function update(Plugin $plugin): void {
        $this->updateOrInstall($plugin);
    }

    public function uninstall(Plugin $plugin): void {
        CssFile::where('plugin_id', $plugin->id)->delete();
        // $this->unpubishFiles($plugin);
    }

    private function updateOrInstall(Plugin $plugin): void {
        DB::transaction(function () use ($plugin) {
            $this->uninstall($plugin);
            $cssFiles = $this->retrieveManifestValues(PluginManifest::fromPlugin($plugin));
            foreach($cssFiles as $cssPath) {
                $path = trim($cssPath);
                if(!is_string($path) || empty($path)) {
                    PluginLog::logWarning("Invalid CSS file path in manifest for plugin {$plugin->name}:" . json_encode($cssPath));
                    continue;
                }

                CssFile::create([
                    'plugin_id' => $plugin->id,
                    'src' => $cssPath,
                ]);
            }
        });

        $this->publish($plugin);
    }

    public function publish(Plugin $plugin): void {
        $cssFiles = $this->retrieveManifestValues(PluginManifest::fromPlugin($plugin));
        foreach($cssFiles as $cssPath) {
            $this->publishFile($plugin, $cssPath);
        }
    }

    private function publishFile(Plugin $plugin, string $cssPath): void {
        
        $pluginPath = PluginDirectory::byPlugin($plugin)->getPluginPath($cssPath);
    
        if(is_link($pluginPath)) {
            $pluginPath = readlink($pluginPath);
        }

        if(file_exists($pluginPath)) {
            $filehandle = fopen($pluginPath, 'r');

            if(!$filehandle) {
                PluginLog::logWarning("Could not open CSS file for plugin {$plugin->name} at path {$pluginPath}.");
                return;
            }
            
            $storageDirectory = new Directory("plugin_css", "private"); // Ensure the target directory exists
            $storageDirectory->store(
                $this->getTargetName($plugin, $pluginPath),
                $filehandle
            );
            fclose($filehandle);
        } else {
            PluginLog::logWarning("CSS file for plugin {$plugin->name} does not exist at path {$pluginPath}.");
        }
    }


    // public function unpublishFiles(Plugin $plugin): void {
    //     $cssFiles = $this->retrieveManifestValues(PluginManifest::fromPlugin($plugin));

    //     foreach($cssFiles as $cssPath) {

    //         Plugin::getDirectory()->delete($this->getPublishedName($cssPath));
    //     }
    // }


    public function retrieveManifestValues(PluginManifest $manifest): array {
        $manifestContent = $manifest->getContent();
        $cssEntries = isset($manifestContent['css']) ? $manifestContent['css'] : [];
        if(!is_array($cssEntries)) {
            PluginLog::logWarning("Invalid CSS entries in manifest for plugin {$manifest->getName()}: " . json_encode($cssEntries));
            return [];
        }
        $srcValues = [];
        foreach($cssEntries as $entry) {
            $src = $cssEntries['file']['@attributes']['src'] ?? null;
            if($src !== null) {
                $srcValues[] = $src;
            } else {
                PluginLog::logWarning("Missing 'src' attribute for CSS file entry in manifest for plugin {$manifest->getName()}: " . json_encode($entry));
            }
        }

        return $srcValues;
    }

    public function verifyManifest(PluginManifest $manifest): bool {
        $cssEntries = $this->retrieveManifestValues($manifest);
        foreach($cssEntries as $cssFile) {
            if(!is_string($cssFile) || empty($cssFile)) {
                return false;
            }
        }

        return true;
    }

    public function createFromJson(Plugin $plugin, array $json): void {
        DB::transaction(function () use ($plugin, $json) {
            foreach($json as $nodes) {
                if(is_array($nodes) && !array_is_list($nodes)) {
                    $this->createSingleEntryFromJson($plugin, $nodes);

                } else {
                    foreach($nodes as $fileNode) {
                        $this->createSingleEntryFromJson($plugin, $fileNode);
                    }
                }
            }
        });
    }

    private function createSingleEntryFromJson(Plugin $plugin, array $json): void {
        if(!isset($json['@attributes']) || empty($json['@attributes']) || !is_array($json['@attributes'])) {
            throw new \Exception("Invalid CSS file entry in manifest for plugin {$plugin->name} on '@attributes': " . json_encode($json));
        }

        $attributes = $json['@attributes'];
        if(!isset($attributes['src']) || empty($attributes['src'])) {
            throw new \Exception("Invalid CSS file entry in manifest for plugin {$plugin->name} on 'src': " . json_encode($attributes));
        }

        $cssFile = new CssFile();
        $cssFile->plugin_id = $plugin->id;
        $cssFile->src = $attributes['src'];
        $cssFile->save();
    }

    // public function addFile(Plugin $plugin, string $src): CssFile{
    //     $cssFile = new CssFile();
    //     $cssFile->plugin_id = $plugin->id;
    //     $cssFile->src = $src;
    //     $cssFile->save();
    //     return $cssFile;
    // }

    // public function addFiles(Plugin $plugin, array $srcs): array {
    //     $cssFiles = [];
    //     foreach($srcs as $src) {
    //         $cssFiles[] = $this->addFile($plugin, $src);
    //     }
    //     return $cssFiles;
    // }

    /**
     * List all file 
     * @param Plugin $plugin
     * @return string[]
     */
    protected function listFiles(Plugin $plugin): array {
        return $this->getData();
    }
    
    public function getStorageDirectory(): Directory {
        return new Directory('plugin_css', 'private');
    }

    protected function getTargetName(Plugin $plugin, mixed $file): string {
        $name = basename(str_replace('\\', '/', (string) $file));
        $name = explode('.', $name)[0];
        $name = preg_replace('/[^a-zA-Z0-9-_]/', '', $name);
        return $plugin->slugName() . '-' . $plugin->uuid . '-' . $name . '.css';
    }

    public function getHtmlTags() {
        $tags = "";
        $installed = app(PluginManager::class)->getInstalledPlugins();
        foreach($installed as $plugin) {
            $urls = $this->getUrls($plugin);
            foreach($urls as $url) {
                $tags .= "<link rel=\"stylesheet\" href=\"{$url}\">\n";
            }
        }
        return $tags;
    }


    public function getUrls(Plugin $plugin): array {
        $files = CssFile::where('plugin_id', $plugin->id)->get();
        $urls = [];
        foreach($files as $cssFile) {
            $scriptUrl = "api/download/plugin/css/" . $this->getTargetName($plugin, $cssFile->src);
            $urls[] = $scriptUrl;
        }
        return $urls;
    }
}