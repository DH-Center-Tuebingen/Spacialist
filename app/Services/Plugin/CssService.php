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
        $this->updateOrInstall($plugin, $manifest);
    }

    public function update(Plugin $plugin, PluginManifest $manifest): void {
        $this->updateOrInstall($plugin, $manifest);
    }

    public function uninstall(Plugin $plugin, PluginManifest $manifest): void {
        CssFile::where('plugin_id', $plugin->id)->delete();
        $this->unpublishFiles($plugin);
    }

    private function updateOrInstall(Plugin $plugin, PluginManifest $manifest): void {
        DB::transaction(function () use ($plugin, $manifest) {
            $this->uninstall($plugin, $manifest);
            $this->createFromManifest($plugin, $manifest);
        });

        $this->publish($plugin, $manifest);
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

    public function publish(Plugin $plugin, PluginManifest $manifest): void {
        $cssFiles = $this->retrieveManifestValues($manifest);
        foreach($cssFiles as $cssPath) {
            $this->publishFile($plugin, $cssPath);
        }
    }

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

            $this->getCssDirectory($plugin)->store(
                $this->getTargetName($plugin, $pluginPath),
                $filehandle
            );
            fclose($filehandle);
        } else {
            PluginLog::logWarning("CSS file for plugin {$plugin->name} does not exist at path {$pluginPath}.");
        }
    }

    public function getCssDirectory(Plugin $plugin): Directory {
        return new Directory("plugin_css", "private");
    }


    public function unpublishFiles(Plugin $plugin): void {
        $cssFiles = $this->retrieveManifestValues(PluginManifest::fromPlugin($plugin));
        $cssDirectory = $this->getCssDirectory($plugin);

        foreach($cssFiles as $cssPath) {
            $cssDirectory->delete($this->getTargetName($plugin, $cssPath));
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