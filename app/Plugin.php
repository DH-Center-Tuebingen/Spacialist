<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
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

    public static function getInstalled() {
        return self::whereNotNull('installed_at')->get();
    }

    public function slugName() {
        return Str::slug($this->name);
    }

    public static function getInfo($path) {
        $infoPath = Str::finish($path, '/') . 'App/info.xml';
        if(!File::isFile($infoPath)) return false;

        $xmlString = file_get_contents($infoPath);
        $xmlObject = simplexml_load_string($xmlString);
        
        return json_decode(json_encode($xmlObject), true);
    }

    public function getMetadata() {
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

    public static function discoverPlugins() {
        $pluginPath = base_path('app/Plugins');
        $availablePlugins = File::directories($pluginPath);
        foreach($availablePlugins as $ap) {
            $info = self::getInfo($ap);
            if($info !== false) {
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
            }
        }
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
        // TODO is it really the same as install?
        $this->handleInstallation();
    }

    public function handleUninstall() {
        $this->rollbackMigrations();
        $this->removeScript();
        $this->removePermissions();
        $this->uninstallPresets();

        $this->installed_at = null;
        $this->save();
    }

    public function handleRemove() {
        // if installed, first rollback migrations and delete all files and presets
        if(isset($this->installed_at)) {
            $this->handleUninstall();
        }

        sp_remove_dir(base_path("app/Plugins/$this->name"));
    }

    public function getPermissions() {
        $pluginPermissionPath = base_path("app/Plugins/$this->name/App/permissions.json");
        if(!File::isFile($pluginPermissionPath)) {
            return [];
        }

        return json_decode(file_get_contents($pluginPermissionPath), true);
    }

    public function getPermissionGroups() {
        return array_keys($this->getPermissions());
    }

    public function getRolePresets() {
        $rolePresets = base_path("app/Plugins/$this->name/App/role-presets.json");
        if(!File::isFile($rolePresets)) {
            return [];
        }

        return json_decode(file_get_contents($rolePresets), true);
    }

    private function getMigrationPath() {
        return base_path("app/Plugins/$this->name/database/migrations");
    }

    private function runMigrations() {
        $migrationPath = $this->getMigrationPath();
        if(file_exists($migrationPath) && is_dir($migrationPath)) {
            $migrations = File::files($migrationPath);
            foreach ($migrations as $migration) {
                $filename = $migration->getFilename();
                Artisan::call('migrate', [
                    '--path' => "/app/Plugins/$this->name/database/migrations/$filename",
                    '--force' => true,
                ]);
            }
        }
    }

    private function rollbackMigrations() {
        $migrationPath = $this->getMigrationPath();
        if(file_exists($migrationPath) && is_dir($migrationPath)) {
            $migrations = File::files($migrationPath);
            // undo migrations in reversed order
            rsort($migrations);
            foreach($migrations as $migration) {
                $filename = $migration->getFilename();
                Artisan::call('migrate:rollback', [
                    '--path' => "/app/Plugins/$this->name/database/migrations/$filename",
                    '--force' => true,
                ]);
            }
        }
    }

    private function publishScript() {
        $name = $this->name;
        $scriptPath = base_path("app/Plugins/$name/js/script.js");
        if(file_exists($scriptPath)) {
            $uuid = $this->uuid;
            $slug = $this->slugName();
            $filehandle = fopen($scriptPath, 'r');
            Storage::put(
                "plugins/${slug}-${uuid}.js",
                $filehandle,
            );
            fclose($filehandle);
        }
    }

    private function removeScript() {
        $uuid = $this->uuid;
        $slug = $this->slugName();
        if(Storage::exists("plugins/${slug}-${uuid}.js")) {
            Storage::delete("plugins/${slug}-${uuid}.js");
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
}
