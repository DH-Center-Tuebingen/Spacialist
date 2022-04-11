<?php

use App\Permission;
use App\Role;
use App\RolePreset;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RestructurePermissions extends Migration
{
    const permissionMap = [
        "create_concepts" => [
            'entity_read',
            'entity_data_read',
            'entity_create',
            'entity_data_create',
            'attribute_create',
            'entity_type_create',
        ],
        "delete_move_concepts" => [
            'entity_read',
            'entity_write',
            'entity_delete',
            'entity_data_delete',
            'attribute_delete',
            'entity_type_delete',
        ],
        "duplicate_edit_concepts" => [
            'entity_read',
            'entity_write',
            'entity_create',
            'entity_data_read',
            'entity_data_write',
            'entity_data_create',
            'attribute_read',
            'attribute_write',
            'attribute_create',
            'entity_type_read',
            'entity_type_write',
            'entity_type_create',
        ],
        "view_concepts" => [
            'entity_read',
            'attribute_read',
            'entity_type_read',
        ],
        "view_concept_props" => [
            'entity_data_read',
        ],
        "edit_bibliography" => [
            'bibliography_read',
            'bibliography_write',
        ],
        "add_remove_bibliography" => [
            'bibliography_read',
            'bibliography_create',
            'bibliography_delete',
        ],
        "manage_files" => [
            '',
        ],
        "link_files" => [
            '',
        ],
        "edit_file_props" => [
            '',
        ],
        "view_files" => [
            '',
        ],
        "export_files" => [
            '',
        ],
        "view_geodata" => [
            '',
        ],
        "create_edit_geodata" => [
            '',
        ],
        "upload_remove_geodata" => [
            '',
        ],
        "link_geodata" => [
            '',
        ],
        "view_users" => [
            'users_roles_read',
        ],
        "create_users" => [
            'users_roles_read',
            'users_roles_create',
        ],
        "delete_users" => [
            'users_roles_read',
            'users_roles_delete',
        ],
        "add_remove_role" => [
            'users_roles_write',
        ],
        "change_password" => [
            'users_roles_write',
        ],
        "add_edit_role" => [
            'users_roles_write',
            'users_roles_create',
        ],
        "delete_role" => [
            'users_roles_delete',
        ],
        "add_remove_permission" => [
            'users_roles_write',
        ],
        "edit_preferences" => [
            'preferences_read',
            'preferences_write',
        ],
        "add_move_concepts_th" => [
            'thesaurus_write',
            'thesaurus_create',
        ],
        "delete_concepts_th" => [
            'thesaurus_delete',
        ],
        "edit_concepts_th" => [
            'thesaurus_write',
        ],
        "export_th" => [
            'thesaurus_share',
        ],
        "view_concepts_th" => [
            'thesaurus_read',
        ],
    ];
    const rolePresets = [
        [
            'name' => 'administrator',
            'rule_set' => [
                'entity_read', 'entity_write', 'entity_create', 'entity_delete', 'entity_share',
                'entity_data_read', 'entity_data_write', 'entity_data_create', 'entity_data_delete', 'entity_data_share',
                'attribute_read', 'attribute_write', 'attribute_create', 'attribute_delete', 'attribute_share',
                'entity_type_read', 'entity_type_write', 'entity_type_create', 'entity_type_delete', 'entity_type_share',
                'bibliography_read', 'bibliography_write', 'bibliography_create', 'bibliography_delete', 'bibliography_share',
                'comments_read', 'comments_write', 'comments_create', 'comments_delete', 'comments_share',
                'users_roles_read', 'users_roles_write', 'users_roles_create', 'users_roles_delete', 'users_roles_share',
                'preferences_read', 'preferences_write', 'preferences_create', 'preferences_delete', 'preferences_share',
                'thesaurus_read', 'thesaurus_write', 'thesaurus_create', 'thesaurus_delete', 'thesaurus_share',
            ],
        ],
        [
            'name' => 'guest',
            'rule_set' => [
                'entity_read',
                'entity_data_read',
                'attribute_read',
                'entity_type_read',
                'bibliography_read',
                'comments_read',
                'users_roles_read',
                'preferences_read',
                'thesaurus_read',
            ],
        ],
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        activity()->disableLogging();
        //
        $oldPermissions = Permission::all();
        $roles = Role::all();
        $newPermissions = sp_get_permission_groups();

        foreach($newPermissions as $group => $permSet) {
            foreach($permSet as $perm) {
                $permission = new Permission();
                $permission->name = $group . "_" . $perm['name'];
                $permission->display_name = $perm['display_name'];
                $permission->description = $perm['description'];
                $permission->guard_name = 'web';
                $permission->save();
            }
        }

        foreach($roles as $role) {
            foreach($role->permissions as $permission) {
                $mappedPermissions = self::permissionMap[$permission->name];
                foreach($mappedPermissions as $mapPerm) {
                    $role->givePermissionTo($mapPerm);
                }
                // $role->revokePermissionTo($permission->name);
            }
        }

        // Delete all old permissions
        // Permission::whereIn('id', $oldPermissions->pluck('id'))->delete();

        Schema::create('role_presets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255);
            $table->jsonb('rule_set');
            $table->timestamps();
        });

        Schema::create('role_preset_plugins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->jsonb('rule_set');
            $table->integer('from');
            $table->integer('extends');
            $table->timestamps();

            $table->foreign('from')->references('id')->on('plugins')->onDelete('cascade');
            $table->foreign('extends')->references('id')->on('role_presets')->onDelete('cascade');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->integer('derived_from')->nullable();

            $table->foreign('derived_from')->references('id')->on('role_presets')->onDelete('cascade');
        });

        foreach(self::rolePresets as $presetDef) {
            $rolePreset = new RolePreset();
            $rolePreset->name = $presetDef['name'];
            $rolePreset->rule_set = $presetDef['rule_set'];
            $rolePreset->save();
        }

        activity()->enableLogging();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        activity()->disableLogging();
        //
        // $oldPermissions = Permission::all();
        // $roles = Role::all();

        // foreach(self::permissionMap as $name => $pm) {
        //     $permission = new Permission();
        //     $permission->name = $name;
        //     $permission->display_name = Str::replace('_', ' ', $name);
        //     $permission->description = '';
        //     $permission->guard_name = 'web';
        //     $permission->save();
        // }

        // foreach($roles as $role) {
        //     foreach($role->permissions as $permission) {
        //         foreach(self::permissionMap as $name => $pm) {
        //             if(Arr::has($pm, $permission->name)) {
        //                 $role->givePermissionTo($name);
        //             }
        //         }
        //         $role->revokePermissionTo($permission->name);
        //     }
        // }

        // // Delete all old permissions
        // Permission::whereIn('id', $oldPermissions->pluck('id'))->delete();

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('derived_from');
        });

        Schema::dropIfExists('role_preset_plugins');
        Schema::dropIfExists('role_presets');

        activity()->enableLogging();
    }
}
