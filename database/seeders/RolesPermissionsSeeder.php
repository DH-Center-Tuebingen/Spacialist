<?php

namespace Database\Seeders;

use App\Role;
use App\Permission;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Seeder;

class RolesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Roles
        // Admin
        $admin = Role::where('name', 'admin')->first();
        if($admin === null) {
            $admin = new Role();
            $admin->name = 'admin';
            $admin->display_name = 'Administrator';
            $admin->description = 'Project Administrator';
            $admin->guard_name = 'web';
            $admin->save();
        }
        // Add all permissions to admin
        $adminPermissions = [
            'entity_read', 'entity_write', 'entity_create', 'entity_delete', 'entity_share',
            'entity_data_read', 'entity_data_write', 'entity_data_create', 'entity_data_delete', 'entity_data_share',
            'attribute_read', 'attribute_write', 'attribute_create', 'attribute_delete', 'attribute_share',
            'entity_type_read', 'entity_type_write', 'entity_type_create', 'entity_type_delete', 'entity_type_share',
            'bibliography_read', 'bibliography_write', 'bibliography_create', 'bibliography_delete', 'bibliography_share',
            'comments_read', 'comments_write', 'comments_create', 'comments_delete', 'comments_share',
            'users_roles_read', 'users_roles_write', 'users_roles_create', 'users_roles_delete', 'users_roles_share',
            'preferences_read', 'preferences_write', 'preferences_create', 'preferences_delete', 'preferences_share',
            'thesaurus_read', 'thesaurus_write', 'thesaurus_create', 'thesaurus_delete', 'thesaurus_share',
        ];

        // Guest
        $guest = Role::where('name', 'guest')->first();
        if($guest === null) {
            $guest = new Role();
            $guest->name = 'guest';
            $guest->display_name = 'Guest';
            $guest->description = 'Guest User';
            $guest->guard_name = 'web';
            $guest->save();
        }
        $guestPermissions = [
            'entity_read',
            'entity_data_read',
            'attribute_read',
            'entity_type_read',
            'bibliography_read',
            'comments_read',
            'users_roles_read',
            'preferences_read',
            'thesaurus_read',
        ];

        // Permissions
        $permissionSets = sp_get_permission_groups();

        foreach($permissionSets as $group => $permSet) {
            foreach($permSet as $perm) {
                $name = $group . "_" . $perm['name'];

                try {
                    $permission = Permission::where('name', $name)->firstOrFail();
                } catch(ModelNotFoundException $e) {
                    $permission = new Permission();
                    $permission->name = $name;
                    $permission->display_name = $perm['display_name'];
                    $permission->description = $perm['description'];
                    $permission->guard_name = 'web';
                    $permission->save();
                }

                if(in_array($name, $adminPermissions)) {
                    $admin->givePermissionTo($permission);
                }
                if(in_array($name, $guestPermissions)) {
                    $guest->givePermissionTo($permission);
                }
            }
        }
    }
}
