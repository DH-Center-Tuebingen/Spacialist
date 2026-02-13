<?php

namespace App\Services;

use App\Plugin;
use App\Models\Plugin\Migration as PluginMigration;
use App\File\Directory;
use App\Permission;
use App\Preference;
use App\Services\RolePresetService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File as FileFacade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


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
        protected HookService $hooks, 
        // AccessPointsService $accessPoints,
        // ScopeService $scopeService,
        protected RolePresetService $rolePresetService
    ) {
        $this->pluggableServices = [
            $hooks,
            // $accessPoints,
            // $scopeService,
            $rolePresetService,
        ];    
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

    private function publishScript(Plugin $plugin): void
    {
        $name = $plugin->name;
        $scriptPath = $plugin->getPath("js/script.js");
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