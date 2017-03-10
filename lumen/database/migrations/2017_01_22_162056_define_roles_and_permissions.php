<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DefineRolesAndPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Permissions
        // Create concepts
        $create_concepts = new App\Permission();
        $create_concepts->name = 'create_concepts';
        $create_concepts->display_name = 'Create concepts';
        $create_concepts->description = 'create concepts';
        $create_concepts->save();
        // Delete/move concepts
        $delete_move_concepts = new App\Permission();
        $delete_move_concepts->name = 'delete_move_concepts';
        $delete_move_concepts->display_name = 'Delete and move concepts';
        $delete_move_concepts->description = 'delete and move previously added concepts';
        $delete_move_concepts->save();
        // Dpulicate/edit concepts
        $duplicate_edit_concepts = new App\Permission();
        $duplicate_edit_concepts->name = 'duplicate_edit_concepts';
        $duplicate_edit_concepts->display_name = 'Duplicate and edit concepts';
        $duplicate_edit_concepts->description = 'duplicate and edit previously added concepts';
        $duplicate_edit_concepts->save();
        // View concepts
        $view_concepts = new App\Permission();
        $view_concepts->name = 'view_concepts';
        $view_concepts->display_name = 'View concepts';
        $view_concepts->description = 'View a list of previously added concepts';
        $view_concepts->save();
        // View concept properties
        $view_concept_props = new App\Permission();
        $view_concept_props->name = 'view_concept_props';
        $view_concept_props->display_name = 'View concepts';
        $view_concept_props->description = 'View a list of previously added concepts';
        $view_concept_props->save();
        // Edit literature
        $edit_literature = new App\Permission();
        $edit_literature->name = 'edit_literature';
        $edit_literature->display_name = 'Edit literature';
        $edit_literature->description = 'edit literature entries';
        $edit_literature->save();
        // Add/remove literature
        $add_remove_literature = new App\Permission();
        $add_remove_literature->name = 'add_remove_literature';
        $add_remove_literature->display_name = 'Add and remove literature';
        $add_remove_literature->description = 'add and remove literature entries';
        $add_remove_literature->save();
        // Manage photos
        $manage_photos = new App\Permission();
        $manage_photos->name = 'manage_photos';
        $manage_photos->display_name = 'Manage photos';
        $manage_photos->description = 'upload and remove photos';
        $manage_photos->save();
        // Link photos
        $link_photos = new App\Permission();
        $link_photos->name = 'link_photos';
        $link_photos->display_name = 'Link photos';
        $link_photos->description = 'link photos to concepts';
        $link_photos->save();
        // Edit photo properties
        $edit_photo_props = new App\Permission();
        $edit_photo_props->name = 'edit_photo_props';
        $edit_photo_props->display_name = 'Edit photo properties';
        $edit_photo_props->description = 'edit the properties of uploaded photos';
        $edit_photo_props->save();
        // View photos
        $view_photos = new App\Permission();
        $view_photos->name = 'view_photos';
        $view_photos->display_name = 'View photos';
        $view_photos->description = 'view uploaded photos';
        $view_photos->save();
        // Export photos
        $export_photos = new App\Permission();
        $export_photos->name = 'export_photos';
        $export_photos->display_name = 'Export photos';
        $export_photos->description = 'export photos in different resolutions and formats';
        $export_photos->save();
        // View geodata
        $view_geodata = new App\Permission();
        $view_geodata->name = 'view_geodata';
        $view_geodata->display_name = 'View geodata';
        $view_geodata->description = 'view geodata';
        $view_geodata->save();
        // Create/edit geodata
        $create_edit_geodata = new App\Permission();
        $create_edit_geodata->name = 'create_edit_geodata';
        $create_edit_geodata->display_name = 'Create and edit geodata';
        $create_edit_geodata->description = 'create and edit uploaded geodata';
        $create_edit_geodata->save();
        // Upload/remove geodata
        $upload_remove_geodata = new App\Permission();
        $upload_remove_geodata->name = 'upload_remove_geodata';
        $upload_remove_geodata->display_name = 'Upload and remove geodata';
        $upload_remove_geodata->description = 'upload new geodata files and remove existing geodata layers';
        $upload_remove_geodata->save();
        // Link geodata
        $link_geodata = new App\Permission();
        $link_geodata->name = 'link_geodata';
        $link_geodata->display_name = 'Link geodata';
        $link_geodata->description = 'link geodata to concepts or other elements';
        $link_geodata->save();
        // View users
        $view_users = new App\Permission();
        $view_users->name = 'view_users';
        $view_users->display_name = 'View users';
        $view_users->description = 'view all existing users';
        $view_users->save();
        // Create users
        $create_users = new App\Permission();
        $create_users->name = 'create_users';
        $create_users->display_name = 'Create users';
        $create_users->description = 'create new users';
        $create_users->save();
        // Delete users
        $delete_users = new App\Permission();
        $delete_users->name = 'delete_users';
        $delete_users->display_name = 'Delete users';
        $delete_users->description = 'delete existing users';
        $delete_users->save();
        // Add/remove role
        $add_remove_role = new App\Permission();
        $add_remove_role->name = 'add_remove_role';
        $add_remove_role->display_name = 'Add and remove roles';
        $add_remove_role->description = 'add and remove roles from a user';
        $add_remove_role->save();
        // Change password
        $change_password = new App\Permission();
        $change_password->name = 'change_password';
        $change_password->display_name = 'Change password';
        $change_password->description = 'change the password of a user';
        $change_password->save();

        // Roles
        // Admin
        $admin = new App\Role();
        $admin->name = 'admin';
        $admin->display_name = 'Administrator';
        $admin->description = 'Project Administrator';
        $admin->save();
        // Add all permissions to admin
        $admin->attachPermission($create_concepts);
        $admin->attachPermission($delete_move_concepts);
        $admin->attachPermission($duplicate_edit_concepts);
        $admin->attachPermission($view_concepts);
        $admin->attachPermission($view_concept_props);
        $admin->attachPermission($edit_literature);
        $admin->attachPermission($add_remove_literature);
        $admin->attachPermission($manage_photos);
        $admin->attachPermission($link_photos);
        $admin->attachPermission($edit_photo_props);
        $admin->attachPermission($view_photos);
        $admin->attachPermission($export_photos);
        $admin->attachPermission($view_geodata);
        $admin->attachPermission($create_edit_geodata);
        $admin->attachPermission($upload_remove_geodata);
        $admin->attachPermission($link_geodata);
        $admin->attachPermission($view_users);
        $admin->attachPermission($create_users);
        $admin->attachPermission($delete_users);
        $admin->attachPermission($add_remove_role);
        $admin->attachPermission($change_password);
        // Guest
        $guest = new App\Role();
        $guest->name = 'guest';
        $guest->display_name = 'Guest';
        $guest->description = 'Guest User';
        $guest->save();
        $guest->attachPermission($view_concepts);
        $guest->attachPermission($view_concept_props);
        $guest->attachPermission($view_photos);
        $guest->attachPermission($view_geodata);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $permissions = [
            'create_concepts', 'delete_move_concepts',
            'duplicate_edit_concepts', 'view_concepts',
            'view_concept_props', 'edit_literature',
            'add_remove_literature', 'manage_photos',
            'link_photos', 'edit_photo_props', 'view_photos',
            'export_photos', 'view_geodata', 'create_edit_geodata',
            'upload_remove_geodata', 'link_geodata', 'view_users', 'create_users',
            'delete_users', 'add_remove_role', 'change_password'
        ];
        foreach($permissions as $p) {
            $entry = App\Permission::where('name', '=', $p)->firstOrFail();
            $entry->delete();
        }
        $roles = [
            'admin', 'guest', 'map_user', 'photo_user',
            'photo_assistent', 'employee'
        ];
        foreach($roles as $r) {
            $entry = App\Role::where('name', '=', $r)->firstOrFail();
            $entry->delete();
        }
    }
}
