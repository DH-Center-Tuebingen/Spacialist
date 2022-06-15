<?php

namespace Database\Seeders;

use App\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionRoleTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        $adminPermissions = [
            'entity_read', 'entity_write', 'entity_create', 'entity_delete', 'entity_share',
            'entity_data_read', 'entity_data_write', 'entity_data_create', 'entity_data_delete', 'entity_data_share',
            'attribute_read', 'attribute_write', 'attribute_create', 'attribute_delete', 'attribute_share',
            'entity_type_read', 'entity_type_write', 'entity_type_create', 'entity_type_delete', 'entity_type_share',
            'bibliography_read', 'bibliography_write', 'bibliography_create', 'bibliography_delete', 'bibliography_share',
            'comments_read', 'comments_write', 'comments_create', 'comments_delete', 'comments_share',
            'users_roles_read', 'users_roles_write', 'users_roles_create', 'users_roles_delete', 'users_roles_share',
            'preferences_read', 'preferences_write', 'preferences_create', 'preferences_delete', 'preferences_share',
        ];
        $guestPermissions = [
            'entity_read',
            'entity_data_read',
            'attribute_read',
            'entity_type_read',
            'bibliography_read',
            'comments_read',
            'users_roles_read',
            'preferences_read',
        ];
        $permissions = Permission::all();

        $insertData = [];
        foreach($permissions as $p) {
            if(in_array($p->name, $adminPermissions)) {
                $insertData[] = [
                    'permission_id' => $p->id,
                    'role_id' => 1,
                ];
            }
            if(in_array($p->name, $guestPermissions)) {
                $insertData[] = [
                    'permission_id' => $p->id,
                    'role_id' => 2,
                ];
            }
        }
        
        DB::table('role_has_permissions')->insert($insertData);
    }
}
