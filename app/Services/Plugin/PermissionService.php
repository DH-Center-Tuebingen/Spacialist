<?php

namespace App\Services\Plugin;

use App\Permission;
use App\Plugin;
use App\Plugin\PluginDirectory;
use App\Plugin\PluginManifest;
use Exception;
use Illuminate\Support\Facades\File;

/**
 * Service for managing user permissions.
 * Permissions are defined inside the "App/permissions.json" in the plugin directory.
 * The permissions.json file should have the following structure:
 * ```json
 * {
 *   "permission_group_1": [
 *     {
 *       "name": "permission_name",
 *       "display_name": "Permission Display Name",
 *       "description": "Description of the permission"
 *     },
 *     ...
 *   ],
 *   "permission_group_2": [
 *     ...
 *   ]
 * }
 * ```
 */
class PermissionService extends PluginService {

    private array $existingPermissions = [];

    public function install(Plugin $plugin, PluginManifest $manifest): void {
        $this->addPermissions($plugin);
    }

    /**
     * For Future Referencec:: We should not remove the permissions on uninstall, as it will
     * also remove the permissions from all roles (which it should), but we should be able to 
     * toggle the plugins off and on without having to reassign the permissions to the roles again. 
     * So we will just keep the permissions in the system, even if the plugin is uninstalled.
     */
    // public function uninstall(Plugin $plugin, PluginManifest $manifest): void {
    //     // $this->removePermissions($plugin);
    // }
    
    public function onRemove(Plugin $plugin, PluginManifest $manifest): void {
        $this->removePermissions($plugin);
    }

    public function onBeforeUpdate(Plugin $plugin, PluginManifest $manifest): void {
        $this->existingPermissions = $this->getPermissions($plugin);
    }

    public function onAfterUpdate(Plugin $plugin, PluginManifest $manifest): void {
        $updatePermissions = $this->getPermissions($plugin);
        try {
            // TODO:: IMPROVE:: For simplicity we currently just assume that the permsets are the same: read, write, delete and export.
            for($existingIndex = count($this->existingPermissions) - 1; $existingIndex >= 0; $existingIndex--) {
                $existingGroup = array_keys($this->existingPermissions)[$existingIndex];
                for($updatedIndex = count($updatePermissions) - 1; $updatedIndex >= 0; $updatedIndex++) {
                    $updateGroup = array_keys($updatePermissions)[$updatedIndex];
                    if($existingGroup === $updateGroup) {
                        array_slice($updatePermissions, $updatedIndex, 1);
                        array_slice($this->existingPermissions, $existingIndex, 1);
                        break;
                    }
                }
            }

            // Remove permissions that are not in the updated manifest anymore
            $this->removePermissionGroups($this->existingPermissions);

            // Add permissions that are new in the updated manifest
            $this->addPermissions($plugin);

        } catch(\Exception $e) {
            $this->existingPermissions = [];
            throw $e;
        }
    }

    /**
     *  Adds permissions defined in the plugin's permissions.json to the system.
     * 
     * @param Plugin $plugin
     * @return void
     */
    private function addPermissions(Plugin $plugin): void {
        $permGroups = $this->getPermissions($plugin);
        $this->addPermissionGroups($permGroups);
    }

    /**
     * Add all permissions defined in the given permission groups to the system.
     * 
     * @param array $permGroups - An associative array where keys are permission group names and values are arrays of permissions. Each permission is an associative array containing 'name', 'display_name', and 'description' keys.
     * @return void
     */
    private function addPermissionGroups(array $permGroups): void {
        foreach($permGroups as $group => $permSet) {
            foreach($permSet as $perm) {
                $permission = $this->createPermission($group, $perm);
                $permission->save();
            }
        }
    }

    /**
     * Creates an unsaved Permission model instance based on the given group and permission data.
     * 
     * @param string $group - The permission group name
     * @param array $permission - An associative array containing 'name', 'display_name', and 'description' keys for the permission
     * @param string $guardName - The guard name for the 'Spatie' permission (default is 'web')
     * @return Permission - Returns an unsaved Permission model instance
     */
    private function createPermission(string $group, array $permission, string $guardName = 'web'): Permission {
        if(!isset ($permission['name'])) {
            throw new Exception("Permission definition is missing 'name' key for group '{$group}'.");
        }
        $permissionModel = new Permission();
        $permissionModel->name = $group . "_" . $permission['name'];
        $permissionModel->display_name = $permission['display_name'];
        $permissionModel->description = $permission['description'];
        $permissionModel->guard_name = $guardName;
        return $permissionModel;
    }

    private function removePermissions(Plugin $plugin): void {
        $permGroups = $this->getPermissions($plugin);
        $this->removePermissionGroups($permGroups);
    }

    private function removePermissionGroups(array $permGroups): void {
        foreach($permGroups as $group => $permSet) {
            foreach($permSet as $perm) {
                Permission::where('name', $group . "_" . $perm['name'])->delete();
            }
        }
    }

    public function getPermissions(Plugin $plugin): mixed {
        $pluginDirectory = PluginDirectory::fromPlugin($plugin);
        $pluginPermissionPath = $pluginDirectory->getAbsolutePluginPath('App/permissions.json');
        if(!File::isFile($pluginPermissionPath)) {
            return [];
        }

        return json_decode(file_get_contents($pluginPermissionPath), true);
    }

    public function getPermissionGroups(Plugin $plugin): array {
        return array_keys($this->getPermissions($plugin));
    }


}