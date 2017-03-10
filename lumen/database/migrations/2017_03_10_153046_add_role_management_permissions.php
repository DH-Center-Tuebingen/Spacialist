<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Role;
use App\Permission;

class AddRoleManagementPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add and edit roles
        $add_edit_role = new Permission();
        $add_edit_role->name = 'add_edit_role';
        $add_edit_role->display_name = 'Add and edit roles';
        $add_edit_role->description = 'add and edit existing roles';
        $add_edit_role->save();
        // Delete roles
        $delete_role = new Permission();
        $delete_role->name = 'delete_role';
        $delete_role->display_name = 'Delete roles';
        $delete_role->description = 'delete existing roles';
        $delete_role->save();
        // Add and remove permissions
        $add_remove_permission = new Permission();
        $add_remove_permission->name = 'add_remove_permission';
        $add_remove_permission->display_name = 'Add and remove permissions';
        $add_remove_permission->description = 'add and remove permissions to/from roles';
        $add_remove_permission->save();

        $admin = Role::where('name', '=', 'admin')->firstOrFail();
        $admin->attachPermission($view_roles);
        $admin->attachPermission($add_edit_role);
        $admin->attachPermission($delete_role);
        $admin->attachPermission($add_remove_permission);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $permissions = [
            'view_roles', 'add_edit_role',
            'delete_role', 'add_remove_permission'
        ];
        foreach($permissions as $p) {
            $entry = Permission::where('name', '=', $p)->firstOrFail();
            $entry->delete();
        }
    }
}
