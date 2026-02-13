<?php

namespace App;

use App\Models\Plugin\Migration as PluginMigration;

use App\File\Directory;
use App\Services\AccessPointsService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Plugin extends Model
{
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

    protected $metadataFields = [
        'authors',
        'description',
        'licence',
        'title',
    ];
    
    public function __construct() { }

    private static function pluginDirectory() {
        $pluginDirectory = config('app.plugin_directory');
        return base_path($pluginDirectory);
    }

    public static function getPluginPath(string $path = ''):string {
        if($path === ''){
            return self::pluginDirectory();
        }
        return self::pluginDirectory() . Str::start($path, '/');
    }

    public static function isInstalled($name): bool {
        return self::whereNotNull('installed_at')->where('name', $name)->exists();
    }

    public static function getInstalled(): Collection {
        return self::whereNotNull('installed_at')->get();
    }

    public function slugName(): string {
        return Str::slug($this->name);
    }

    public function getPath(string $path = ''): string {
        $pluginPath = $this->name;
        if($path !== ''){
            $pluginPath .= Str::start($path, '/');
        }
        return self::getPluginPath($pluginPath);
    }

    public function publicName($withPath = true): string {
        $slug = $this->slugName();
        $uuid = $this->uuid;
        $name = "{$slug}-{$uuid}.js";
        if($withPath) {
            $name = "plugins/$name";
        }
        return $name;
    }

    public static function getPluginInfo($path, $isString = false): mixed {
        if(!$isString) {
            $infoPath = Str::finish($path, '/') . 'App/info.xml';
            if(!File::isFile($infoPath)) return false;
            $xmlString = file_get_contents($infoPath);
        } else {
            $xmlString = $path;
        }

        $xmlObject = simplexml_load_string($xmlString);
        return json_decode(json_encode($xmlObject), true);
    }

    public function getInfo(){
        return self::getPluginInfo($this->getPath());
    }

    public function getMetadata(): array {
        $info = $this->getInfo();
        if($info !== false) {
            $metadata = [];
            foreach($this->metadataFields as $field) {
                if($field == 'authors') {
                    if(!array_key_exists($field, $info) || !array_key_exists('author', $info[$field])) {
                        $metadata[$field] = [];
                        continue;
                    }

                    $authors = $info[$field]['author'];
                    $metadata[$field] = is_array($authors) ? $authors : [$authors];
                } else {
                    if(!array_key_exists($field, $info)) {
                        $metadata[$field] = "";
                        continue;
                    }
                    $metadata[$field] = $info[$field];
                }
            }
            return $metadata;
        } else {
            return [];
        }
    }

    public function getChangelog(?string $since = null): string {
        $changelog = $this->getPath('CHANGELOG.md');
        if(!File::isFile($changelog)) return '';
        $changes = file_get_contents($changelog);
        if(isset($since) && preg_match("/\\n#+\s(v\s?)?$since(\s-\s.+)?\\n/i", $changes, $matches, PREG_OFFSET_CAPTURE) !== false) {
            if(count($matches) > 0) {
                $changes = substr($changes, 0, $matches[0][1]);
            }
        }
        return $changes;
    }

    public function getAccessPoints(): array {
        $info = self::getInfo();
        $accesspoints = [];
        $addedNames = [];
        $addedPaths = [];
        if($info !== false) {
            if(array_key_exists('accesspoints', $info)) {
                foreach($info['accesspoints'] as $accesspoint) {
                    $name = $this->name . '-' . $accesspoint['id'];
                    $label = $accesspoint['label'];
                    $path = Str::finish(Str::start($accesspoint['path'], '/'), '/');
                    // $path = '/' . $this->slugName() . Str::finish(Str::start($accesspoint['url'], '/'), '/');
                    if(array_key_exists($name, $addedNames)) {
                        throw new \Exception("An accesspoint with the name ($name) already exists");
                    }
                    if(array_key_exists($path, $addedPaths)) {
                        throw new \Exception("An accesspoint with the path ($path) already exists");
                    }

                    $addedNames[$name] = true;
                    $addedPaths[$path] = true;

                    $accesspoints[$name] = [
                        'label' => $label,
                        'path' => $path,
                    ];
                }
            }
        }
        return $accesspoints;
    }

    private function getScopeCacheKey(): string {
        return 'plugin_scopes_' . $this->id;
    }

    public function getScopes(): array {
        return Cache::rememberForever($this->getScopeCacheKey(), function() {
            $info = self::getInfo();
            $scopes = [];
            if($info !== false) {
                if(array_key_exists('scopes', $info)) {
                    foreach($info['scopes'] as $scope) {
                        $attributes = $scope['@attributes'];
                        if(!array_key_exists('src', $attributes)) {
                            Log::error('<scope> attribute \'src\' is required');
                            continue;
                        }
                        if(!array_key_exists('on', $attributes)) {
                            Log::error('<scope> attribute \'on\' is required');
                            continue;
                        }

                        $src = $attributes['src'];
                        $on = $attributes['on'];

                        $srcDir = $this->getPath("Scopes");
                        if(!file_exists($srcDir) || !is_dir($srcDir)) {
                            Log::error('Missing \'Scopes\' directory');
                            continue;
                        }
                        $srcPath = $srcDir . DIRECTORY_SEPARATOR . $src;
                        if(!file_exists($srcPath)) {
                            Log::error("Missing file '$src'");
                            continue;
                        }
                        if(!class_exists($on)) {
                            Log::error("Class '{$on}' does not exist!");
                            continue;
                        }
                        $className = Str::replaceEnd('.php', '', $src);
                        $namespacedSrc = $this->getNamespace("\\Scopes\\$className");

                        if(!array_key_exists($on, $scopes)) {
                            $scopes[$on] = [];
                        }

                        $scopes[$on][] = $namespacedSrc;
                    }
                }
            }
            return $scopes;
        });
    }
    
    /**
     * Get the namespace for a given path within the plugin. If no path is provided, 
     * returns the base namespace for the plugin.
     * 
     * @param string|null $path Optional path within the plugin to get the namespace for, separated 
     * by backslashes or forward slashes. For example, "Controllers/MyController.php" or 
     * "Controllers\MyController.php". Can start with or without a leading slash.
     */
    public function getNamespace(string $path = null): string{
        $basePath = "App\\Plugins\\$this->name";
        if($path) {
            $basePath .= Str::start(str_replace('/', '\\', $path), '\\');
        }
        return $basePath;
    }

    /**
     * Get all scopes defined in Plugins for a given model class (e.g. App\Entity).
     */
    public static function getScopesFor(string $modelClass) {
        $scopes = [];

        $installedPlugins = Plugin::getInstalled();
        foreach($installedPlugins as $plugin) {
            $pluginScopes = $plugin->getScopes();
            if(array_key_exists($modelClass, $pluginScopes)) {
                foreach($pluginScopes[$modelClass] as $scope) {
                    $scopes[] = $scope;
                }
            }
        }

        return $scopes;
    }

    public function getRegisteredAttributes(): array {
        $info = $this->getInfo();
        $attributes = [];
        if($info !== false) {
            if(array_key_exists('attributes', $info)) {
                $attributes = $info['attributes']['attribute'];
                // If only one <attribute> exists, this <attribute> is returned
                // instead of an array, but we always want an array
                if(array_key_exists('@attributes', $attributes)) {
                    $attributes = [$attributes];
                }
            }
        }
        return $attributes;
    }

    public static function updateOrCreateFromInfo(array $info): Plugin {
        $id = $info['name'];
        $plugin = self::where('name', $id)->first();
        // discovered new Plugin, add it to DB
        if(!isset($plugin)) {
            $plugin = new self();
            $plugin->name = $id;
            $plugin->version = $info['version'];
            $plugin->uuid = Str::uuid();
            $plugin->save();
        } else {
            $plugin->updateUpdateState($info['version']);
        }

        return $plugin;
    }

    public static function updateState(): void {
        $availablePlugins = File::directories(self::getPluginPath());
        self::discoverPlugins($availablePlugins);
        self::cleanupPlugins($availablePlugins);
    }

    public static function cleanupPlugins(array $list): void {
        $pluginNames = [];

        foreach($list as $p) {
            $pluginNames[] = File::basename($p);
        }

        $nonExistingPlugins = self::whereNotIn('name', $pluginNames)->get();

        foreach($nonExistingPlugins as $removedPlugin) {
            $removedPlugin->handleRemove();
        }
    }

    public static function discoverPlugins(array $list): void {
        foreach($list as $ap) {
            $info = self::getPluginInfo($ap);
            if($info !== false) {
                self::updateOrCreateFromInfo($info);
            }
        }
    }

    public static function discoverPluginByName($name): ?Plugin {
        $pluginPath = self::getPluginPath($name);
        $info = self::getPluginInfo($pluginPath);
        if($info === false) {
            return null;
        }

        $plugin = self::updateOrCreateFromInfo($info);
        return $plugin;
    }

    public static function getWithMetadata() {
        self::updateState();
        $plugins = self::all();

        foreach($plugins as $plugin) {
            $plugin->metadata = $plugin->getMetadata();
            $plugin->changelog = $plugin->getChangelog();
        }
        return $plugins;
    }

    public static function getDirectory(): Directory {
        return new Directory('plugins');
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
                $this->update_available = null;
            }
            $this->save();
        }
    }

    public function clearCache(): void {
        app(\App\Services\PluginManager::class)->clearCache($this);
    }

    public function handleInstallation(bool $isUpdate = false): void {
        app(\App\Services\PluginManager::class)->install($this, $isUpdate);
    }

    public function handleUpdate(): string {
        return app(\App\Services\PluginManager::class)->update($this);
    }

    public function handleUninstall(): void {
        app(\App\Services\PluginManager::class)->uninstall($this);
    }

    public function handleRemove(): void {
        // if installed, first rollback migrations and delete all files and presets
        app(\App\Services\PluginManager::class)->remove($this);
    }

    public function getPermissions(): mixed {
        $pluginPermissionPath = $this->getPath('App/permissions.json');
        if(!File::isFile($pluginPermissionPath)) {
            return [];
        }

        return json_decode(file_get_contents($pluginPermissionPath), true);
    }

    public function getPermissionGroups(): array {
        return array_keys($this->getPermissions());
    }

    public function getRolePresets(): mixed {
        return [];
    }

    public function getMigrationState(): array {
        return PluginMigration::getMigrationState($this);
    }

    public function runMigrations(): void {
        app(\App\Services\PluginManager::class)->runMigrations($this);
    }

    public function rollbackMigrations(): void {
        app(\App\Services\PluginManager::class)->rollbackMigrations($this);
    }

    // private function uninstallPresets(): void {
    //     RolePresetPlugin::where('from', $this->id)->delete();
    // }

    // private function removePreferences(): void {
    //     $id = Str::kebab($this->name);
    //     Preference::where('label', 'ilike', "plugin.$id.%")->delete();
    // }
    
}
