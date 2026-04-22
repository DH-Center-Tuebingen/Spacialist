<?php

namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Plugin extends Model {
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'installed_at' => 'datetime',
    ];

    // private static function pluginDirectory() {
    //     $pluginDirectory = config('app.plugin_directory');
    //     return base_path($pluginDirectory);
    // }

    // public static function getDirectoryPath(string $path = ''): string {
    //     if($path === '') {
    //         return self::pluginDirectory();
    //     }
    //     return self::pluginDirectory() . Str::start($path, '/');
    // }
    
    public static function getInstalledPlugins(): Collection {
        return self::whereNotNull('installed_at')->get();
    }

    public static function isInstalled($name): bool {
        return self::whereNotNull('installed_at')->where('name', $name)->exists();
    }

    public function slugName(): string {
        return Str::slug($this->name);
    }

    // public function getPath(string $path = ''): string {
    //     $pluginPath = $this->name;
    //     if($path !== '') {
    //         $pluginPath .= Str::start($path, '/');
    //     }
    //     return self::getDirectoryPath($pluginPath);
    // }

    // public function publicName($withPath = true): string {
    //     $slug = $this->slugName();
    //     $uuid = $this->uuid;
    //     $name = "{$slug}-{$uuid}.js";
    //     if($withPath) {
    //         $name = "plugins/$name";
    //     }
    //     return $name;
    // }

    // public function getMetadata(): array {
    //     $info = $this->getInfo();
    //     if($info !== FALSE) {
    //         $metadata = [];
    //         foreach($this->metadataFields as $field) {
    //             if($field == 'authors') {
    //                 if(!array_key_exists($field, $info) || !array_key_exists('author', $info[$field])) {
    //                     $metadata[$field] = [];
    //                     continue;
    //                 }

    //                 $authors = $info[$field]['author'];
    //                 $metadata[$field] = is_array($authors) ? $authors : [$authors];
    //             } else {
    //                 if(!array_key_exists($field, $info)) {
    //                     $metadata[$field] = "";
    //                     continue;
    //                 }
    //                 $metadata[$field] = $info[$field];
    //             }
    //         }
    //         return $metadata;
    //     } else {
    //         return [];
    //     }
    // }

    // public function getChangelog(?string $since = NULL): string {
    //     $changelog = $this->getPath('CHANGELOG.md');
    //     if(!File::isFile($changelog))
    //         return '';
    //     $changes = file_get_contents($changelog);
    //     if(isset($since) && preg_match("/\\n#+\s(v\s?)?$since(\s-\s.+)?\\n/i", $changes, $matches, PREG_OFFSET_CAPTURE) !== FALSE) {
    //         if(count($matches) > 0) {
    //             $changes = substr($changes, 0, $matches[0][1]);
    //         }
    //     }
    //     return $changes;
    // }



    /**
     * Get the namespace for a given path within the plugin. If no path is provided, 
     * returns the base namespace for the plugin.
     * 
     * @param string|null $path Optional path within the plugin to get the namespace for, separated 
     * by backslashes or forward slashes. For example, "Controllers/MyController.php" or 
     * "Controllers\MyController.php". Can start with or without a leading slash.
     */
    public function getNamespace(string $path = NULL): string {
        $basePath = "App\\Plugins\\$this->name";
        if($path) {
            $basePath .= Str::start(str_replace('/', '\\', $path), '\\');
        }
        return $basePath;
    }

    public static function updateOrCreateFromInfo(array $info): Plugin {
        $name = $info['name'];
        $plugin = self::where('name', $name)->first();
        // discovered new Plugin, add it to DB
        
        if(!isset($plugin)) {
            $plugin = new self();
            $plugin->name = $name;
            $plugin->version = $info['version'];
            $plugin->uuid = Str::uuid();
            $plugin->save();
        } else {
            $plugin->updateUpdateState($info['version']);
        }

        return $plugin;
    }

    // public static function updateState(): void {
    //     $availablePlugins = File::directories(self::getDirectoryPath());
    //     self::discoverPlugins($availablePlugins);
    //     self::cleanupPlugins($availablePlugins);
    // }

    // public static function cleanupPlugins(array $list): void {
    //     $pluginNames = [];

    //     foreach($list as $p) {
    //         $pluginNames[] = File::basename($p);
    //     }

    //     $nonExistingPlugins = self::whereNotIn('name', $pluginNames)->get();
    //     foreach($nonExistingPlugins as $removedPlugin) {
    //         info("Plugin '{$removedPlugin->name}' does not exist anymore and will be removed from database.");
    //         $removedPlugin->handleRemove();
    //     }
    // }

    // public static function discoverPlugins(array $list): void {
    //     foreach($list as $ap) {
    //         $info = self::getPluginInfo($ap);
    //         if($info !== FALSE) {
    //             self::updateOrCreateFromInfo($info);
    //         }
    //     }
    // }

    // public static function discoverPluginByName($name): ?Plugin {
    //     $pluginPath = self::getDirectoryPath($name);
    //     $info = self::getPluginInfo($pluginPath);
    //     if($info === FALSE) {
    //         return NULL;
    //     }

    //     $plugin = self::updateOrCreateFromInfo($info);
    //     return $plugin;
    // }

    public static function getWithMetadata() {
        app(PluginManager::class)->discover();
        $plugins = self::all();

        foreach($plugins as $plugin) {
            $plugin->metadata = $plugin->getMetadata();
            $plugin->changelog = $plugin->getChangelog();
        }
        return $plugins;
    }



    public function updateUpdateState($fromInfoVersion): void {
        if($this->version != $fromInfoVersion) {
            // installed version splitted
            preg_match('/(\d+)\.(\d+).(\d+)(-.+)?/', $this->version, $iv);
            // available/latest version splitted
            preg_match('/(\d+)\.(\d+).(\d+)(-.+)?/', $fromInfoVersion, $lv);

            if(
                ($lv[1] > $iv[1] || $lv[2] > $iv[2] || $lv[3] > $iv[3]) ||
                (!isset($lv[4]) && isset($iv[4])) ||
                (isset($lv[4]) && isset($iv[4]) && $lv[4] > $iv[4])
            ) {
                $this->update_available = $fromInfoVersion;
            } else {
                $this->update_available = NULL;
            }
            $this->save();
        }
    }


    // public function getMigrationState(): array {
    //     return PluginMigration::getMigrationState($this);
    // }

    // public function runMigrations(): void {
    //     app(\App\Services\PluginManager::class)->runMigrations($this);
    // }

    // public function rollbackMigrations(): void {
    //     app(\App\Services\PluginManager::class)->rollbackMigrations($this);
    // }

    // private function uninstallPresets(): void {
    //     RolePresetPlugin::where('from', $this->id)->delete();
    // }

    // private function removePreferences(): void {
    //     $id = Str::kebab($this->name);
    //     Preference::where('label', 'ilike', "plugin.$id.%")->delete();
    // }

}
