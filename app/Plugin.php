<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class Plugin extends Model
{
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];

    protected $metadataFields = [
        'authors',
        'description',
        'licence',
        'title',
    ];
    
    public static function getPathFor(string $name){
        $name = str_replace('/', DIRECTORY_SEPARATOR, $name);
        return base_path("app" . DIRECTORY_SEPARATOR . "Plugins" . DIRECTORY_SEPARATOR . $name);
    }

    public static function isInstalled($name) {
        return self::whereNotNull('installed_at')->where('name', $name)->exists();
    }

    public static function getInstalled() {
        return self::whereNotNull('installed_at')->get();
    }
    
    public function getPath(?string $path = null){
        $pluginPath = $this->name;
        if(isset($path)) {
            $pluginPath = $pluginPath . DIRECTORY_SEPARATOR . $path; 
        }
        
        return self::getPathFor($pluginPath);
    }

    public function slugName() {
        return Str::slug($this->name);
    }

    public function publicName($withPath = true) {
        $slug = $this->slugName();
        $uuid = $this->uuid;
        $name = "$slug-$uuid.js";
        if($withPath) {
            $name = "plugins" . DIRECTORY_SEPARATOR . $name;
        }
        return $name;
    }

    public static function getInfoFor(string $path, bool $isString = false) {
        if(!$isString) {
            $infoPath = Str::finish($path, DIRECTORY_SEPARATOR) . 'App' . DIRECTORY_SEPARATOR .'info.xml';
            if(!File::isFile($infoPath)) return false;
            $xmlString = file_get_contents($infoPath);
        } else {
            $xmlString = $path;
        }

        $xmlObject = simplexml_load_string($xmlString);
        
        return json_decode(json_encode($xmlObject), true);
    }
    
    public function getInfo(){
        return self::getInfoFor($this->getPath());
    }

    public function getMetadata() {
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

    public function getChangelog($since = null) {
        $changelog = Str::finish($this->getPath(), DIRECTORY_SEPARATOR) . 'CHANGELOG.md';
        if(!File::isFile($changelog)) return '';
        $changes = file_get_contents($changelog);
        if(isset($since) && preg_match("/\\n#+\s(v\s?)?$since(\s-\s.+)?\\n/i", $changes, $matches, PREG_OFFSET_CAPTURE) !== false) {
            if(count($matches) > 0) {
                $changes = substr($changes, 0, $matches[0][1]);
            }
        }
        return $changes;
    }

    public static function updateOrCreateFromInfo(array $info) : Plugin {
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

    public static function updateState() : void {
        $pluginPath = base_path('app'. DIRECTORY_SEPARATOR .'Plugins');
        $availablePlugins = File::directories($pluginPath);

        self::discoverPlugins($availablePlugins);
        self::cleanupPlugins($availablePlugins);
    }

    public static function cleanupPlugins(array $list) : void {
        $pluginNames = [];

        foreach($list as $p) {
            $pluginNames[] = File::basename($p);
        }

        $nonExistingPlugins = self::whereNotIn('name', $pluginNames)->get();

        foreach($nonExistingPlugins as $removedPlugin) {
            $removedPlugin->handleRemove();
        }
    }

    public static function discoverPlugins(array $list) : void {
        foreach($list as $ap) {
            $info = self::getInfoFor($ap);
            if($info !== false) {
                self::updateOrCreateFromInfo($info);
            }
        }
    }

    public static function discoverPluginByName($name) : Plugin|null {
        $pluginPath = self::getPathFor($name);
        $info = self::getInfoFor($pluginPath);
        if($info === false) {
            return null;
        }

        $plugin = self::updateOrCreateFromInfo($info);

        return $plugin;
    }

    public function updateUpdateState($fromInfoVersion) {
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

    public function handleInstallation() {
        $this->runMigrations();
        $this->publishScript();
        $this->addPermissions();
        $this->installPresetsFromFile();

        $this->installed_at = Carbon::now();
        $this->save();
    }

    public function handleUpdate() {
        $oldVersion = $this->version;
        // TODO is it really the same as install?
        $this->handleInstallation();


        $info = $this->getInfo();
        $this->update_available = null;
        $this->version = $info['version'];
        $this->save();
        return $oldVersion;
    }

    public function handleUninstall() {
        $this->removeScript();

        $this->installed_at = null;
        $this->save();
    }

    public function handleRemove() {
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

    public function getPermissions() {
        $pluginPermissionPath = $this->getPath("/App/permissions.json");
        if(!File::isFile($pluginPermissionPath)) {
            return [];
        }

        return json_decode(file_get_contents($pluginPermissionPath), true);
    }

    public function getPermissionGroups() {
        return array_keys($this->getPermissions());
    }

    public function getRolePresets() {
        $rolePresets = self::getPathFor("$this->name/App/role-presets.json");
        if(!File::isFile($rolePresets)) {
            return [];
        }

        return json_decode(file_get_contents($rolePresets), true);
    }

    private function getClassWithPrefix($path, $classname) {
        return "App\\Plugins\\$this->name\\$path\\$classname";
    }

    private function getMigrationPath(?string $migration = null) {
        $path = "$this->name/Migration";
        
        if(isset($migration)) {
            $path = "$path/$migration";
        }
        
        return self::getPathFor($path);
    }
    
    private function getSortedMigrations(bool $desc = false) : array {
        $migrationPath = $this->getMigrationPath();
        if(file_exists($migrationPath) && is_dir($migrationPath)) {
            $migrations = collect(File::files($migrationPath))->map(function($f) {
                return $f->getFilename();
            });
            if($desc) {
                $migrations = $migrations->sortDesc();
            } else {
                $migrations = $migrations->sort();
            }

            return $migrations->values()->toArray();
        }

        return [];
    }
    
    private function getMigrationClassInstance($migration){
        preg_match("/^[1-9]\d{3}_\d{2}_\d{2}_\d{6}_(.*)\.php$/", $migration, $matches);
        if(count($matches) != 2) return null;

        $className = Str::studly($matches[1]);
        require($this->getMigrationPath($migration));
        $prefixedClassName = $this->getClassWithPrefix('Migration', $className);
        return new $prefixedClassName();
    }

    private function runMigrations() {
        foreach($this->getSortedMigrations() as $migration) {
            $instance = $this->getMigrationClassInstance($migration);
            if($instance === null) continue;
            call_user_func([$instance, 'migrate']);
        }
    }

    private function rollbackMigrations() {
        foreach($this->getSortedMigrations(true) as $migration) {
            $instance = $this->getMigrationClassInstance($migration);
            if($instance === null) continue;
            call_user_func([$instance, 'rollback']);
        }
    }

    public function linkScript() {
        $scriptPath = $this->getPath("js/script.js");
        if(file_exists($scriptPath)) {
            $storageLink = Storage::path($this->publicName());
            if(file_exists($storageLink)) {
                unlink($storageLink);
            }
            File::link($scriptPath, $storageLink);
        } else {
            throw new \Exception("Could not find script file for plugin $this->name");
        }
    }

    public function publishScript() {
        $scriptPath = $this->getPath("js/script.js");
        if(file_exists($scriptPath)) {
            $filehandle = fopen($scriptPath, 'r');
            Storage::put(
                $this->publicName(),
                $filehandle,
            );
            fclose($filehandle);
        } else {
            Log::error("Could not find script file for plugin $this->name");
        }
    }

    private function removeScript() {
        $path = $this->publicName();
        if(Storage::exists($path)) {
            Storage::delete($path);
        }
    }

    private function addPermissions() {
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

    private function removePermissions() {
        $permGroups = $this->getPermissions();
        foreach($permGroups as $group => $permSet) {
            foreach($permSet as $perm) {
                Permission::where('name', $group . "_" . $perm['name'])->delete();
            }
        }
    }

    private function installPresetsFromFile() {
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

    private function uninstallPresets() {
        RolePresetPlugin::where('from', $this->id)->delete();
    }

    private function removePreferences() {
        $id = Str::kebab($this->name);
        Preference::where('label', 'ilike', "plugin.$id.%")->delete();
    }
}
