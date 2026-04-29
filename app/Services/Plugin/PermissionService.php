<?php

namespace App\Services\Plugin;

use App\Permission;
use App\Plugin;
use App\Plugin\PluginDirectory;
use App\RolePreset;
use App\RolePresetPlugin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Service for managing role presets.
 * 
 * 
 */
class PermissionService extends PluginService {

    /**
     * Install role presets defined in a plugin's role-presets.json
     */
    public function install(Plugin $plugin): void {
        $this->addPermissions($plugin);
    }

    /**
     * Remove all role presets that belong to a plugin
     */
    public function uninstall(Plugin $plugin): void {
        $this->removePermissions($plugin);
    }
    
    public function onBeforeUpdate(Plugin $plugin): void
    {
        $this->uninstall($plugin);
    }
    
    public function onAfterUpdate(Plugin $plugin): void
    {
        $this->install($plugin);
    }

    /**
     * 
     * @param Plugin $plugin
     * @return void
     */
    private function addPermissions(Plugin $plugin): void {
        $permGroups = $this->getPermissions($plugin);
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

    private function removePermissions(Plugin $plugin): void {
        $permGroups = $this->getPermissions($plugin);
        foreach($permGroups as $group => $permSet) {
            foreach($permSet as $perm) {
                Permission::where('name', $group . "_" . $perm['name'])->delete();
            }
        }
    }

    public function getPermissions(Plugin $plugin): mixed { 
        $pluginDirectory = new PluginDirectory($plugin);
        $pluginPermissionPath = $pluginDirectory->getPluginPath('App/permissions.json');
        if(!File::isFile($pluginPermissionPath)) {
            return [];
        }

        return json_decode(file_get_contents($pluginPermissionPath), true);
    }

    public function getPermissionGroups(Plugin $plugin): array {
        return array_keys($this->getPermissions($plugin));
    }


}