<?php

use App\Role;
use App\Permission;
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
            $admin->save();
        }
        // Add all permissions to admin

        // Guest
        $guest = Role::where('name', 'guest')->first();
        if($guest === null) {
            $guest = new Role();
            $guest->name = 'guest';
            $guest->display_name = 'Guest';
            $guest->description = 'Guest User';
            $guest->save();
        }

        // Permissions
        // Create concepts
        if(Permission::where('name', 'create_concepts')->first() === null) {
            $create_concepts = new Permission();
            $create_concepts->name = 'create_concepts';
            $create_concepts->display_name = 'Create concepts';
            $create_concepts->description = 'create concepts';
            $create_concepts->save();
            $admin->attachPermission($create_concepts);
        }
        // Delete/move concepts
        if(Permission::where('name', 'delete_move_concepts')->first() === null) {
            $delete_move_concepts = new Permission();
            $delete_move_concepts->name = 'delete_move_concepts';
            $delete_move_concepts->display_name = 'Delete and move concepts';
            $delete_move_concepts->description = 'delete and move previously added concepts';
            $delete_move_concepts->save();
            $admin->attachPermission($delete_move_concepts);
        }
        // Dpulicate/edit concepts
        if(Permission::where('name', 'duplicate_edit_concepts')->first() === null) {
            $duplicate_edit_concepts = new Permission();
            $duplicate_edit_concepts->name = 'duplicate_edit_concepts';
            $duplicate_edit_concepts->display_name = 'Duplicate and edit concepts';
            $duplicate_edit_concepts->description = 'duplicate and edit previously added concepts';
            $duplicate_edit_concepts->save();
            $admin->attachPermission($duplicate_edit_concepts);
        }
        // View concepts
        if(Permission::where('name', 'view_concepts')->first() === null) {
            $view_concepts = new Permission();
            $view_concepts->name = 'view_concepts';
            $view_concepts->display_name = 'View concepts';
            $view_concepts->description = 'View a list of previously added concepts';
            $view_concepts->save();
            $admin->attachPermission($view_concepts);
            $guest->attachPermission($view_concepts);
        }
        // View concept properties
        if(Permission::where('name', 'view_concept_props')->first() === null) {
            $view_concept_props = new Permission();
            $view_concept_props->name = 'view_concept_props';
            $view_concept_props->display_name = 'View concepts';
            $view_concept_props->description = 'View a list of previously added concepts';
            $view_concept_props->save();
            $admin->attachPermission($view_concept_props);
            $guest->attachPermission($view_concept_props);
        }
        // Edit literature
        if(Permission::where('name', 'edit_literature')->first() === null) {
            $edit_literature = new Permission();
            $edit_literature->name = 'edit_literature';
            $edit_literature->display_name = 'Edit literature';
            $edit_literature->description = 'edit literature entries';
            $edit_literature->save();
            $admin->attachPermission($edit_literature);
        }
        // Add/remove literature
        if(Permission::where('name', 'add_remove_literature')->first() === null) {
            $add_remove_literature = new Permission();
            $add_remove_literature->name = 'add_remove_literature';
            $add_remove_literature->display_name = 'Add and remove literature';
            $add_remove_literature->description = 'add and remove literature entries';
            $add_remove_literature->save();
            $admin->attachPermission($add_remove_literature);
        }
        // Manage photos
        if(Permission::where('name', 'manage_photos')->first() === null) {
            $manage_photos = new Permission();
            $manage_photos->name = 'manage_photos';
            $manage_photos->display_name = 'Manage photos';
            $manage_photos->description = 'upload and remove photos';
            $manage_photos->save();
            $admin->attachPermission($manage_photos);
        }
        // Link photos
        if(Permission::where('name', 'link_photos')->first() === null) {
            $link_photos = new Permission();
            $link_photos->name = 'link_photos';
            $link_photos->display_name = 'Link photos';
            $link_photos->description = 'link photos to concepts';
            $link_photos->save();
            $admin->attachPermission($link_photos);
        }
        // Edit photo properties
        if(Permission::where('name', 'edit_photo_props')->first() === null) {
            $edit_photo_props = new Permission();
            $edit_photo_props->name = 'edit_photo_props';
            $edit_photo_props->display_name = 'Edit photo properties';
            $edit_photo_props->description = 'edit the properties of uploaded photos';
            $edit_photo_props->save();
            $admin->attachPermission($edit_photo_props);
        }
        // View photos
        if(Permission::where('name', 'view_photos')->first() === null) {
            $view_photos = new Permission();
            $view_photos->name = 'view_photos';
            $view_photos->display_name = 'View photos';
            $view_photos->description = 'view uploaded photos';
            $view_photos->save();
            $admin->attachPermission($view_photos);
            $guest->attachPermission($view_photos);
        }
        // Export photos
        if(Permission::where('name', 'export_photos')->first() === null) {
            $export_photos = new Permission();
            $export_photos->name = 'export_photos';
            $export_photos->display_name = 'Export photos';
            $export_photos->description = 'export photos in different resolutions and formats';
            $export_photos->save();
            $admin->attachPermission($export_photos);
        }
        // View geodata
        if(Permission::where('name', 'view_geodata')->first() === null) {
            $view_geodata = new Permission();
            $view_geodata->name = 'view_geodata';
            $view_geodata->display_name = 'View geodata';
            $view_geodata->description = 'view geodata';
            $view_geodata->save();
            $admin->attachPermission($view_geodata);
            $guest->attachPermission($view_geodata);
        }
        // Create/edit geodata
        if(Permission::where('name', 'create_edit_geodata')->first() === null) {
            $create_edit_geodata = new Permission();
            $create_edit_geodata->name = 'create_edit_geodata';
            $create_edit_geodata->display_name = 'Create and edit geodata';
            $create_edit_geodata->description = 'create and edit uploaded geodata';
            $create_edit_geodata->save();
            $admin->attachPermission($create_edit_geodata);
        }
        // Upload/remove geodata
        if(Permission::where('name', 'upload_remove_geodata')->first() === null) {
            $upload_remove_geodata = new Permission();
            $upload_remove_geodata->name = 'upload_remove_geodata';
            $upload_remove_geodata->display_name = 'Upload and remove geodata';
            $upload_remove_geodata->description = 'upload new geodata files and remove existing geodata layers';
            $upload_remove_geodata->save();
            $admin->attachPermission($upload_remove_geodata);
        }
        // Link geodata
        if(Permission::where('name', 'link_geodata')->first() === null) {
            $link_geodata = new Permission();
            $link_geodata->name = 'link_geodata';
            $link_geodata->display_name = 'Link geodata';
            $link_geodata->description = 'link geodata to concepts or other elements';
            $link_geodata->save();
            $admin->attachPermission($link_geodata);
        }
        // View users
        if(Permission::where('name', 'view_users')->first() === null) {
            $view_users = new Permission();
            $view_users->name = 'view_users';
            $view_users->display_name = 'View users';
            $view_users->description = 'view all existing users';
            $view_users->save();
            $admin->attachPermission($view_users);
        }
        // Create users
        if(Permission::where('name', 'create_users')->first() === null) {
            $create_users = new Permission();
            $create_users->name = 'create_users';
            $create_users->display_name = 'Create users';
            $create_users->description = 'create new users';
            $create_users->save();
            $admin->attachPermission($create_users);
        }
        // Delete users
        if(Permission::where('name', 'delete_users')->first() === null) {
            $delete_users = new Permission();
            $delete_users->name = 'delete_users';
            $delete_users->display_name = 'Delete users';
            $delete_users->description = 'delete existing users';
            $delete_users->save();
            $admin->attachPermission($delete_users);
        }
        // Add/remove role
        if(Permission::where('name', 'add_remove_role')->first() === null) {
            $add_remove_role = new Permission();
            $add_remove_role->name = 'add_remove_role';
            $add_remove_role->display_name = 'Add and remove roles';
            $add_remove_role->description = 'add and remove roles from a user';
            $add_remove_role->save();
            $admin->attachPermission($add_remove_role);
        }
        // Change password
        if(Permission::where('name', 'change_password')->first() === null) {
            $change_password = new Permission();
            $change_password->name = 'change_password';
            $change_password->display_name = 'Change password';
            $change_password->description = 'change the password of a user';
            $change_password->save();
            $admin->attachPermission($change_password);
        }
        // Add and edit roles
        if(Permission::where('name', 'add_edit_role')->first() === null) {
            $add_edit_role = new Permission();
            $add_edit_role->name = 'add_edit_role';
            $add_edit_role->display_name = 'Add and edit roles';
            $add_edit_role->description = 'add and edit existing roles';
            $add_edit_role->save();
            $admin->attachPermission($add_edit_role);
        }
        // Delete roles
        if(Permission::where('name', 'delete_role')->first() === null) {
            $delete_role = new Permission();
            $delete_role->name = 'delete_role';
            $delete_role->display_name = 'Delete roles';
            $delete_role->description = 'delete existing roles';
            $delete_role->save();
            $admin->attachPermission($delete_role);
        }
        // Add and remove permissions
        if(Permission::where('name', 'add_remove_permission')->first() === null) {
            $add_remove_permission = new Permission();
            $add_remove_permission->name = 'add_remove_permission';
            $add_remove_permission->display_name = 'Add and remove permissions';
            $add_remove_permission->description = 'add and remove permissions to/from roles';
            $add_remove_permission->save();
            $admin->attachPermission($add_remove_permission);
        }
        // Edit system preferences
        if(Permission::where('name', 'edit_preferences')->first() === null) {
            $edit_preferences = new Permission();
            $edit_preferences->name = 'edit_preferences';
            $edit_preferences->display_name = 'Edit system preferences';
            $edit_preferences->description = 'edit system preferences';
            $edit_preferences->save();
            $admin->attachPermission($edit_preferences);
        }
        // Add & Move thesaurus concepts
        if(Permission::where('name', 'add_move_concepts_th')->first() === null) {
            $add_move_concepts_th = new Permission();
            $add_move_concepts_th->name = 'add_move_concepts_th';
            $add_move_concepts_th->display_name = 'Add, move and relations of thesaurus concepts';
            $add_move_concepts_th->description = 'add, move and add relations to concepts in thesaurex';
            $add_move_concepts_th->save();
            $admin->attachPermission($add_move_concepts_th);
        }
        // Delete thesaurus concepts
        if(Permission::where('name', 'delete_concepts_th')->first() === null) {
            $delete_concepts_th = new Permission();
            $delete_concepts_th->name = 'delete_concepts_th';
            $delete_concepts_th->display_name = 'Delete thesaurus concepts';
            $delete_concepts_th->description = 'delete concepts in thesaurex';
            $delete_concepts_th->save();
            $admin->attachPermission($delete_concepts_th);
        }
        // Edit thesaurus concepts
        if(Permission::where('name', 'edit_concepts_th')->first() === null) {
            $edit_concepts_th = new Permission();
            $edit_concepts_th->name = 'edit_concepts_th';
            $edit_concepts_th->display_name = 'Edit thesaurus concepts';
            $edit_concepts_th->description = 'edit (modify labels) concepts in thesaurex';
            $edit_concepts_th->save();
            $admin->attachPermission($edit_concepts_th);
        }
        // Export thesaurus concepts
        if(Permission::where('name', 'export_th')->first() === null) {
            $export_th = new Permission();
            $export_th->name = 'export_th';
            $export_th->display_name = 'Export thesaurus concepts';
            $export_th->description = 'export (sub-)trees in thesaurex';
            $export_th->save();
            $admin->attachPermission($export_th);
        }
        // View thesaurus concepts
        if(Permission::where('name', 'view_concepts_th')->first() === null) {
            $view_concepts_th = new Permission();
            $view_concepts_th->name = 'view_concepts_th';
            $view_concepts_th->display_name = 'View thesaurus concepts';
            $view_concepts_th->description = 'view concepts in thesaurex';
            $view_concepts_th->save();
            $admin->attachPermission($view_concepts_th);
            $guest->attachPermission($view_concepts_th);
        }
    }
}
