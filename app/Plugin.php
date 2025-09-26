<?php

namespace App;

use App\File\Directory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
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
                    $authors = $info[$field]['author'];
                    $metadata[$field] = is_array($authors) ? $authors : [$authors];
                } else {
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
        info(self::getPluginPath());
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

    public function handleInstallation(bool $isUpdate = false): void {
        $this->runMigrations();
        $this->publishScript();
        $this->addPermissions();
        $this->installPresetsFromFile();

        if(!$isUpdate) {
            $this->installed_at = Carbon::now();
        }
        $this->save();
    }

    public function handleUpdate(): string {
        $oldVersion = $this->version;
        // TODO is it really the same as install?
        $this->handleInstallation(true);
        $info = $this->getInfo();
        $this->update_available = null;
        $this->version = $info['version'];
        $this->save();
        return $oldVersion;
    }

    public function handleUninstall(): void {
        $this->removeScript();

        $this->installed_at = null;
        $this->save();
    }

    public function handleRemove(): void {
        // if installed, first rollback migrations and delete all files and presets
        if(isset($this->installed_at)) {
            $this->handleUninstall();
            $this->rollbackMigrations();
            $this->removePermissions();
            $this->uninstallPresets();
        }

        $this->removePreferences();
        sp_remove_dir($this->getPath());
        $this->delete();
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
        $rolePresets = $this->getPath('App/role-presets.json');
        if(!File::isFile($rolePresets)) {
            return [];
        }

        return json_decode(file_get_contents($rolePresets), true);
    }

    private function getClassWithPrefix($path, $classname): string {
        return "App\\Plugins\\$this->name\\$path\\$classname";
    }

    private function getMigrationPath(): string {
        return $this->getPath('Migration');
    }
    private function getSortedMigrations(bool $desc = false): array {
        $migrationPath = $this->getMigrationPath();
        info($migrationPath);
        if(file_exists($migrationPath) && is_dir($migrationPath)) {
            $migrations = collect(File::files($migrationPath))->map(function($f) {
                return $f->getFilename();
            });
            if($desc) {
                $migrations = $migrations->sortDesc();
            } else {
                $migrations = $migrations->sort();
            }

            info("Found migrations: " . implode(", ", $migrations->toArray()));
            return $migrations->values()->toArray();
        }
        info("No migration path found.");
        return [];
    }

    private function runMigrations(): void {
        foreach($this->getSortedMigrations() as $migration) {
            preg_match("/^[1-9]\d{3}_\d{2}_\d{2}_\d{6}_(.*)\.php$/", $migration, $matches);
            if(count($matches) != 2) continue;

            $className = Str::studly($matches[1]);
            require($this->getPath("Migration/$migration"));
            $prefixedClassName = $this->getClassWithPrefix('Migration', $className);
            $instance = new $prefixedClassName();
            call_user_func([$instance, 'migrate']);
        }
    }

    private function rollbackMigrations(): void {
        foreach($this->getSortedMigrations(true) as $migration) {
            preg_match("/^[1-9]\d{3}_\d{2}_\d{2}_\d{6}_(.*)\.php$/", $migration, $matches);
            if(count($matches) != 2) continue;

            $className = Str::studly($matches[1]);
            require($this->getPath("Migration/$migration"));
            $prefixedClassName = $this->getClassWithPrefix('Migration', $className);
            $instance = new $prefixedClassName();
            call_user_func([$instance, 'rollback']);
        }
    }

    private function publishScript(): void {
        $name = $this->name;
        $scriptPath = $this->getPath("js/script.js");
        if(file_exists($scriptPath)) {
            $filehandle = fopen($scriptPath, 'r');

            if(!$filehandle) {
                throw new \Exception("Could not open script file for plugin $name.");
            }

            self::getDirectory()->store(
                $this->publicName(false),
                $scriptPath
            );
            fclose($filehandle);
        } else {
            throw new \Exception("Script file for plugin $name does not exist at $scriptPath.");
        }
    }

    private function removeScript(): void {
        self::getDirectory()->delete($this->publicName(false));
    }

    private function addPermissions(): void {
        $permGroups = $this->getPermissions();
        foreach($permGroups as $group => $permSet) {
            foreach($permSet as $perm) {
                $permission = new Permission();
                $permission->name = $group . "_" . $perm['name'];
                $permission->display_name = $perm['display_name'];
                $permission->description = $perm['description'];
                $permission->guard_name = 'web';
                $permission->save();
            }
        }
    }

    private function removePermissions(): void {
        $permGroups = $this->getPermissions();
        foreach($permGroups as $group => $permSet) {
            foreach($permSet as $perm) {
                Permission::where('name', $group . "_" . $perm['name'])->delete();
            }
        }
    }

    private function installPresetsFromFile(): void {
        $rolePresets = $this->getRolePresets();
        foreach($rolePresets as $preset) {
            $baseRolePreset = RolePreset::where('name', $preset['extends'])->firstOrFail();
            $pluginPreset = new RolePresetPlugin();
            $pluginPreset->rule_set = $preset['rule_set'];
            $pluginPreset->extends = $baseRolePreset->id;
            $pluginPreset->from = $this->id;
            $pluginPreset->save();
        }
    }

    private function uninstallPresets(): void {
        RolePresetPlugin::where('from', $this->id)->delete();
    }

    private function removePreferences(): void {
        $id = Str::kebab($this->name);
        Preference::where('label', 'ilike', "plugin.$id.%")->delete();
    }
}
