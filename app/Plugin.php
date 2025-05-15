<?php

namespace App;

use App\File\Directory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

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

    public static function isInstalled($name): bool {
        return self::whereNotNull('installed_at')->where('name', $name)->exists();
    }

    public static function getInstalled(): Collection {
        return self::whereNotNull('installed_at')->get();
    }

    public function slugName(): string {
        return Str::slug($this->name);
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

    public static function getInfo($path, $isString = false): mixed {
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

    public function getMetadata(): array {
        $info = self::getInfo(base_path("app/Plugins/$this->name"));
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
        $changelog = Str::finish(base_path("app/Plugins/$this->name"), '/') . 'CHANGELOG.md';
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
        $info = self::getInfo(base_path("app/Plugins/$this->name"));
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
        $pluginPath = base_path('app/Plugins');
        $availablePlugins = File::directories($pluginPath);

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
            $info = self::getInfo($ap);
            if($info !== false) {
                self::updateOrCreateFromInfo($info);
            }
        }
    }

    public static function discoverPluginByName($name): ?Plugin {
        $pluginPath = base_path("app/Plugins/$name");
        $info = self::getInfo($pluginPath);
        if($info === false) {
            return null;
        }

        $plugin = self::updateOrCreateFromInfo($info);

        return $plugin;
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

    public function handleInstallation(): void {
        $this->runMigrations();
        $this->publishScript();
        $this->addPermissions();
        $this->installPresetsFromFile();

        $this->installed_at = Carbon::now();
        $this->save();
    }

    public function handleUpdate(): string {
        $oldVersion = $this->version;
        // TODO is it really the same as install?
        $this->handleInstallation();


        $info = self::getInfo(base_path("app/Plugins/$this->name"));
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
        sp_remove_dir(base_path("app/Plugins/$this->name"));

        $this->delete();
    }

    public function getPermissions(): mixed {
        $pluginPermissionPath = base_path("app/Plugins/$this->name/App/permissions.json");
        if(!File::isFile($pluginPermissionPath)) {
            return [];
        }

        return json_decode(file_get_contents($pluginPermissionPath), true);
    }

    public function getPermissionGroups(): array {
        return array_keys($this->getPermissions());
    }

    public function getRolePresets(): mixed {
        $rolePresets = base_path("app/Plugins/$this->name/App/role-presets.json");
        if(!File::isFile($rolePresets)) {
            return [];
        }

        return json_decode(file_get_contents($rolePresets), true);
    }

    private function getClassWithPrefix($path, $classname): string {
        return "App\\Plugins\\$this->name\\$path\\$classname";
    }

    private function getMigrationPath(): string {
        return base_path("app/Plugins/$this->name/Migration");
    }
    private function getSortedMigrations(bool $desc = false): array {
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

    private function runMigrations(): void {
        foreach($this->getSortedMigrations() as $migration) {
            preg_match("/^[1-9]\d{3}_\d{2}_\d{2}_\d{6}_(.*)\.php$/", $migration, $matches);
            if(count($matches) != 2) continue;

            $className = Str::studly($matches[1]);
            require(base_path("app/Plugins/$this->name/Migration/$migration"));
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
            require(base_path("app/Plugins/$this->name/Migration/$migration"));
            $prefixedClassName = $this->getClassWithPrefix('Migration', $className);
            $instance = new $prefixedClassName();
            call_user_func([$instance, 'rollback']);
        }
    }

    private function publishScript(): void {
        $name = $this->name;
        $scriptPath = base_path("app/Plugins/$name/js/script.js");
        if(file_exists($scriptPath)) {
            $scriptDirectory = self::getDirectory();
            $scriptDirectory->store($this->publicName(false), $scriptPath);
        }
    }

    private function removeScript(): void {
        $path = $this->publicName();
        self::getDirectory()->delete($this->publicName());
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
