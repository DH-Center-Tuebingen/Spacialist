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
 *        <file src="/Path/To/style.css" />
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

    public function install(Plugin $plugin, PluginManifest $manifest): void {
        $this->markDirty();
        $this->updateOrInstall($plugin, $manifest);
    }

    public function update(Plugin $plugin, PluginManifest $manifest): void {
        $this->markDirty();
        $this->updateOrInstall($plugin, $manifest);
    }

    public function uninstall(Plugin $plugin, PluginManifest $manifest): void {
        CssFile::where('plugin_id', $plugin->id)->delete();
        $this->unpublishFiles($plugin);
        $this->markDirty();
    }

    private function updateOrInstall(Plugin $plugin, PluginManifest $manifest): void {
        DB::transaction(function () use ($plugin, $manifest) {
            $this->uninstall($plugin, $manifest);
            $this->createFromManifest($plugin, $manifest);
        });

        $this->publish($plugin);
    }

    public function createFromManifest(Plugin $plugin, PluginManifest $manifest): void {
        $cssFiles = $this->retrieveManifestValues($manifest);
        $this->createFromArray($cssFiles, $plugin);
    }

    public function createFromArray(array $cssEntries, Plugin $plugin): void {
        foreach($cssEntries as $cssPath) {
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
    }

    /**
     * Publish the css files to the storage directory.
     * @param Plugin $plugin
     * @return void
     */
    public function publish(Plugin $plugin): void {
        // We must take the installed files as it already skipped invalid entries in the manifest.
        $cssFiles = $this->getData();
        foreach($cssFiles as $cssPath) {
            $this->publishFile($plugin, $cssPath['src']);
        }
    }

    /**
     * Publish a single file to the storage directory.
     * 
     * @param Plugin $plugin
     * @param string $cssPath Path of the css file relative to the plugin root. If the file is not found a warning will be issued in the `plugin.log`.
     * @return void
     */
    private function publishFile(Plugin $plugin, string $cssPath): void {
        $pluginPath = PluginDirectory::fromPlugin($plugin)->getAbsolutePluginPath($cssPath);
        if(is_link($pluginPath)) {
            $pluginPath = readlink($pluginPath);
        }

        if(file_exists($pluginPath)) {
            $filehandle = fopen($pluginPath, 'r');

            if(!$filehandle) {
                PluginLog::logWarning("Could not open CSS file for plugin {$plugin->name} at path {$pluginPath}.");
                return;
            }            
            $this->getStorageDirectory()->store(
                $this->getTargetName($plugin, $pluginPath),
                $filehandle
            );
            fclose($filehandle);
        } else {
            PluginLog::logWarning("CSS file for plugin {$plugin->name} does not exist at path {$pluginPath}.");
        }
    }
    
    /**
     * Unpublish all CSS files specified inside the manifest.
     * @param Plugin $plugin
     * @return void
     */
    public function unpublishFiles(Plugin $plugin): void {
        $cssFiles = $this->retrieveManifestValues(PluginManifest::fromPlugin($plugin));
        $cssDirectory = $this->getStorageDirectory();
        foreach($cssFiles as $cssFilePath) {
            $cssFileName = $this->getTargetName($plugin, $cssFilePath);
            $result = $cssDirectory->deleteFile($cssFileName);
            
            if(!$result) {
                PluginLog::logWarning("Failed to delete CSS file for plugin {$plugin->name} at path {$cssFileName}.");
            }
        }
    }


    public function retrieveManifestValues(PluginManifest $manifest): array {
        $cssEntries = $manifest->getTagNodes("css/file");
        if(!is_array($cssEntries)) {
            PluginLog::logWarning("Invalid CSS entries in manifest for plugin {$manifest->getName()}: " . json_encode($cssEntries));
            return [];
        }
        $srcValues = [];
        foreach($cssEntries as $entry) {
            $src = $entry['attributes']['src'] ?? null;
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


    /**
     * List all file 
     * @param Plugin $plugin
     * @return string[]
     */
    protected function listFiles(Plugin $plugin): array {
        return $this->getData();
    }

    /**
     * Retrieves the storage directory for the published CSS files.
     * @return Directory
     */
    public function getStorageDirectory(): Directory {
        return new Directory("plugin_css", "public");
    }

    /**
     * Get's the name of the CSS
     * @param Plugin $plugin
     * @param mixed $file
     * @return string
     */
    protected function getTargetName(Plugin $plugin, string $fileName): string {
        $name = basename(str_replace('\\', '/', (string) $fileName));
        $name = explode('.', $name)[0];
        $name = preg_replace('/[^a-zA-Z0-9-_]/', '', $name);
        return $plugin->slugName() . '-' . $plugin->uuid . '-' . $name . '.css';
    }

    /**
     * Get's all CSS files as HTML tags.
     * @return string All CSS files in HTML link tags separated by newline.
     */
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


    /**
     * Returns all CSS download URLs.
     * @param Plugin $plugin
     * @return string[]
     */
    public function getUrls(Plugin $plugin): array {
        $files = CssFile::where('plugin_id', $plugin->id)->get();
        $urls = [];
        foreach($files as $cssFile) {
            $scriptUrl = "storage/plugin_css/" . $this->getTargetName($plugin, $cssFile->src) . "?$plugin->version";
            $urls[] = $scriptUrl;
        }
        return $urls;
    }
}