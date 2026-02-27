<?php

namespace App\Services;

use App\Plugin;
use App\Models\Plugin\Migration as PluginMigration;
use App\Permission;
use App\Preference;
use App\Services\RolePresetService;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;


/**
 * A service class that manages all plugin related business logic,
 * such as installation, updates, and uninstallation of plugins.
 * 
 * It orchestrates the various features of the plugin system, 
 * such as hooks, migrations, permissions, ... .
 */

class PluginManager
{
     
    private array $pluggableServices = [];

    public function __construct(
        public HookService $hooks, 
        // AccessPointsService $accessPoints,
        // public MigrationService $migrationService,
        public RolePresetService $rolePresetService
    ) {
        $this->pluggableServices = [
            $hooks,
            // $accessPoints,
            // $migrationService,
            $rolePresetService,
        ];    
     }

     public function rebuildPluginCache(){
        //Iterate over Plugin directory and cache all available plugins
        // $plugins = require(base_path('app/Plugins'));

        $dirs = File::allDirectories(base_path('app/Plugins'));
        $cachedPlugins = [];
        foreach($dirs as $dir) {
            $info = Plugin::getPluginInfo($dir);
            if($info !== false) {
                $cachedPluginInfo = [
                    'name' => $info['name'],
                    'version' => $info['version'],
                    'provider' => null,
                ];
                $cachedPlugins[] = $cachedPluginInfo;
            }
        }
        $cacheContent = "<?php\n\nreturn " . var_export($cachedPlugins, true) . ";\n";
        File::put(base_path('bootstrap/cache/plugins.php'), $cacheContent);
     }

    public  function getCachedPlugins(){
        try{
            $plugins = require(base_path('bootstrap/cache/plugins.php'));
        }catch(\Exception $e){
            $this->rebuildPluginCache();
            $plugins = require(base_path('bootstrap/cache/plugins.php'));
        }
        return $plugins;
     }

    public function install(Plugin $plugin): void
    {
        foreach($this->pluggableServices as $service) {
            $service->install($plugin);
        }
        
        $this->runMigrations($plugin);
        $this->publishScript($plugin);
        $this->addPermissions($plugin);
        $this->clearCache($plugin);
        $plugin->installed_at = Carbon::now();
        $plugin->save();
    }

    public function update(Plugin $plugin): string
    {
        $oldVersion = $plugin->version;
        
        foreach($this->pluggableServices as $service) {
            $service->update($plugin);   
        }
        
        // $this->install($plugin, true);
        $info = $plugin->getInfo();
        $plugin->update_available = null;
        $plugin->version = $info['version'];
        $plugin->save();
        return $oldVersion;
    }

    public function uninstall(Plugin $plugin): void
    {
        foreach($this->pluggableServices as $service) {
            $service->uninstall($plugin);   
        }
        
        $this->removeScript($plugin);
        $this->clearCache($plugin);
        $plugin->installed_at = null;
        $plugin->save();
    }

    public function remove(Plugin $plugin): void
    {
        if(isset($plugin->installed_at)) {
            $this->uninstall($plugin);
            $this->rollbackMigrations($plugin);
            $this->removePermissions($plugin);
        }
        
        foreach($this->pluggableServices as $service) {
            $service->remove($plugin);   
        }

        $this->removePreferences($plugin);
        sp_remove_dir($plugin->getPath());
        $plugin->delete();
    }

    public function clearCache(Plugin $plugin): void
    {
        
    }

    public function runMigrations(Plugin $plugin): void
    {
        PluginMigration::run($plugin);
    }

    public function rollbackMigrations(Plugin $plugin): void
    {
        PluginMigration::rollback($plugin);
    }

    public function publishScript(Plugin $plugin): void
    {
        $name = $plugin->name;
        $scriptPath = $plugin->getPath("js/script.js");
        
        if(is_link($scriptPath)) {
            $scriptPath = readlink($scriptPath);
        }
        
        if(file_exists($scriptPath)) {
            $filehandle = fopen($scriptPath, 'r');

            if(! $filehandle) {
                throw new \Exception("Could not open script file for plugin $name.");
            }

            Plugin::getDirectory()->store(
                $plugin->publicName(false),
                $filehandle
            );
            fclose($filehandle);
        } else {
            throw new \Exception("Script file for plugin $name does not exist at $scriptPath.");
        }
    }

    private function removeScript(Plugin $plugin): void
    {
        Plugin::getDirectory()->delete($plugin->publicName(false));
    }

    private function addPermissions(Plugin $plugin): void
    {
        $permGroups = $plugin->getPermissions();
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

    private function removePermissions(Plugin $plugin): void
    {
        $permGroups = $plugin->getPermissions();
        foreach($permGroups as $group => $permSet) {
            foreach($permSet as $perm) {
                Permission::where('name', $group . "_" . $perm['name'])->delete();
            }
        }
    }

    private function removePreferences(Plugin $plugin): void
    {
        $id = Str::kebab($plugin->name);
        Preference::where('label', 'ilike', "plugin.$id.%")->delete();
    }
}