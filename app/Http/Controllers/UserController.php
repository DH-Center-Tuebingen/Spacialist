<?php

namespace App\Http\Controllers;

use App\Attribute;
use App\Entity;
use App\File;
use App\Permission;
use App\Role;
use App\User;
use App\Http\Controllers\Controller;
use App\Plugin;
use App\RolePreset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Sleep;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    // GET

    public function refreshToken() {
        return response()->json([
            'status' => 'success'
        ]);
    }

    public function getUser(Request $request) {
        $user = User::with('notifications')->find(auth()->user()->id);
        $user->setPermissions();

        // Load notification source data into info property
        $user->notifications->map(function($n) {
            if($n->type == 'App\Notifications\CommentPosted') {
                $skip = false;
                switch($n->data['resource']['type']) {
                    case 'App\Entity':
                        try {
                            $name = Entity::findOrFail($n->data['resource']['id'])->name;
                        } catch (ModelNotFoundException $e) {
                            $skip = true;
                        }
                        break;
                    case 'App\AttributeValue':
                    case 'attribute_values':
                        try {
                            $name = Entity::findOrFail($n->data['resource']['meta']['entity_id'])->name;
                            $attrUrl = Attribute::findOrFail($n->data['resource']['meta']['attribute_id'])->thesaurus_url;
                        } catch (ModelNotFoundException $e) {
                            $skip = true;
                        }
                        break;
                    default:
                        $skip = true;
                        break;
                }
                if(!$skip) {
                    $data = [
                        'name' => $name,
                    ];
                    if(isset($attrUrl)) {
                        $data['attribute_url'] = $attrUrl;
                    }
                    
                    $n->info = $data;
                }
            } else if($n->type == 'App\Notifications\EntityUpdated') {
                try {
                    $n->info = [
                        'name' => Entity::findOrFail($n->data['resource']['id'])->name,
                    ];
                } catch (ModelNotFoundException $e) {
                }
            }
            return $n;
        });

        return response()->json([
            'status' => 'success',
            'data' => $user
        ]);
    }

    public function getUsers() {
        $user = auth()->user();
        if(!$user->can('users_roles_read')) {
            return response()->json([
                'error' => __('You do not have the permission to view users')
            ], 403);
        }
        $users = User::with('roles')->withoutTrashed()->orderBy('id')->get();
        $delUsers = User::with('roles')->onlyTrashed()->orderBy('id')->get();

        return response()->json([
            'users' => $users,
            'deleted_users' => $delUsers,
        ]);
    }

    public function getRoles() {
        $user = auth()->user();
        if(!$user->can('users_roles_read')) {
            return response()->json([
                'error' => __('You do not have the permission to view roles')
            ], 403);
        }
        $roles = Role::with(['permissions', 'derived'])->orderBy('id')->get();
        $perms = Permission::orderBy('id')->get();
        $presets = RolePreset::all();

        return response()->json([
            'roles' => $roles,
            'permissions' => $perms,
            'presets' => $presets,
        ]);
    }

    public function getAccessGroups(Request $request) {
        $user = auth()->user();
        // TODO which perm?
        if(!$user->can('users_roles_read')) {
            return response()->json([
                'error' => __('You do not have the permission to view roles')
            ], 403);
        }

        $withPlugins = $request->query('plugins');

        $groups['core'] = sp_get_permission_groups(true);

        if($withPlugins) {
            $installedPlugins = Plugin::getInstalled();
            $groups['plugins'] = [];
            foreach($installedPlugins as $plugin) {
                $slug = $plugin->slugName();
                $groups['plugins'][$slug] = $plugin->getPermissionGroups();
            }
        }

        return response()->json($groups);
    }

    // POST

    public function login(Request $request) {
        $this->validate($request, [
            'email' => 'required_without:nickname|email|max:255',
            'nickname' => 'required_without:email|alpha_dash|max:255',
            'password' => 'required'
        ]);

        $creds = ['password'];
        if($request->has('nickname')) {
            $creds[] = 'nickname';
            $user = User::where('nickname', $request->get('nickname'))->withoutTrashed()->first();
        } else {
            $creds[] = 'email';
            $user = User::where('email', $request->get('email'))->withoutTrashed()->first();
        }
        if(!isset($user)) {
            Sleep::for(2)->seconds();
            return response()->json([
                'error' => __('Invalid Credentials')
            ], 400);
        }
        if($user->login_attempts === 0) {
            return response()->json([
                'error' => __('Password confirmation expired')
            ], 400);
        }
        $credentials = request($creds);

        if(!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => __('Invalid Credentials')], 400);
        }

        if($user->login_attempts > 0) {
            $user->login_attempts--;
            $user->save();
        }

        return response()
            ->json(null, 200)
            ->header('Authorization', $token);
    }

    public function addUser(Request $request) {
        $user = auth()->user();
        if(!$user->can('users_roles_create')) {
            return response()->json([
                'error' => __('You do not have the permission to add new users')
            ], 403);
        }
        $this->validate($request, [
            'email' => 'required_without:nickname|email|max:255|unique:users,email',
            'nickname' => 'required_without:email|alpha_dash|max:255|unique:users,nickname',
            'name' => 'required|string|max:255',
            'password' => 'required|min:6',
        ]);

        $name = $request->get('name');
        $nickname = $request->get('nickname');
        $email = $request->get('email');
        $password = Hash::make($request->get('password'));

        $user = new User();
        $user->name = $name;
        $user->nickname = Str::lower($nickname);
        $user->email = Str::lower($email);
        $user->password = $password;
        $user->save();
        $user = User::find($user->id);

        return response()->json($user);
    }

    public function addAvatar(Request $request) {
        $user = auth()->user();
        $this->validate($request, [
            'file' => 'required|file',
        ]);

        try {
            $user = User::findOrFail($user->id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This user does not exist')
            ], 400);
        }

        $file = $request->file('file');
        $path = $user->uploadAvatar($file);
        $user->avatar = $path;
        $user->save();

        // return user without roles relation
        $user->unsetRelation('roles');

        return response()->json($user);
    }

    public function addRole(Request $request) {
        $user = auth()->user();
        if(!$user->can('users_roles_create')) {
            return response()->json([
                'error' => __('You do not have the permission to add roles')
            ], 403);
        }
        $this->validate($request, Role::rules);

        $role = new Role();
        $role->guard_name = 'web';
        foreach($request->only(array_keys(Role::rules)) as $key => $value) {
            $role->{$key} = $value;
        }

        if($request->has('derived_from')) {
            $preset = RolePreset::find($request->get('derived_from'));
            $permissions = $preset->fullSet;
            $role->syncPermissions($permissions);
        }

        $role->save();
        $role = Role::find($role->id);
        $role->load(['derived', 'permissions']);

        return response()->json($role);
    }

    public function logout(Request $request) {
        auth()->logout(true);
        auth()->invalidate(true);
    }

    // PATCH

    public function patchUser(Request $request, $id) {
        $user = auth()->user();

        if(!$user->can('users_roles_write')) {
            return response()->json([
                'error' => __('You do not have the permission to modify user data')
            ], 403);
        }
        $this->validate($request, [
            'roles' => 'array',
            'email' => 'email',
            'name' => 'string|max:255',
            'nickname' => 'alpha_dash|max:255|unique:users,nickname',
            'phonenumber' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:255',
            'field' => 'nullable|string|max:255',
            'institution' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'orcid' => 'nullable|orcid',
        ]);

        if(!$this->hasInput($request)) {
            return response()->json(null, 204);
        }

        try {
            $user = User::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This user does not exist')
            ], 400);
        }

        // Check if another user with the desired email address
        // is already added. If so, return with failed validation
        if($request->has('email')) {
            $userWithMail = User::where('email', $request->get('email'))->first();
            if(isset($userWithMail) && $userWithMail->id != $id) {
                $error = ValidationException::withMessages([
                    'email' => [__('validation.unique', ['attribute' => 'email'])]
                ]);
                throw $error;
            }
        }

        if($request->has('roles')) {
            $user->roles()->detach();
            $roles = $request->get('roles');
            $user->assignRole($roles);

            // Update updated_at column
            $user->touch();
        }
        if($request->has('email')) {
            $user->email = Str::lower($request->get('email'));
            $user->save();
        }
        if($request->has('name')) {
            $user->name = $request->get('name');
            $user->save();
        }
        if($request->has('nickname')) {
            $user->nickname = Str::lower($request->get('nickname'));
            $user->save();
        }
        $user->setMetadata(
            $request->only('phonenumber', 'orcid', 'role', 'field', 'institution', 'department')
        );

        // return user without roles relation
        $user->unsetRelation('roles');

        return response()->json($user);
    }

    public function restoreUser($id)
    {
        $user = auth()->user();
        if (!$user->can('users_roles_delete')) {
            return response()->json([
                'error' => __('You do not have the permission to restore users')
            ], 403);
        }

        try {
            $delUser = User::onlyTrashed()->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This user does not exist')
            ], 400);
        }

        $delUser->restore();
        return response()->json(null, 204);
    }

    public function patchRole(Request $request, $id) {
        $user = auth()->user();
        if(!$user->can('users_roles_write')) {
            return response()->json([
                'error' => __('You do not have the permission to set role permissions')
            ], 403);
        }
        $this->validate($request, [
            'permissions' => 'array',
            'is_moderated' => 'boolean',
            'display_name' => 'string',
            'description' => 'string'
        ]);

        if(!$this->hasInput($request)) {
            return response()->json(null, 204);
        }

        try {
            $role = Role::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This role does not exist')
            ], 400);
        }

        if($request->has('permissions')) {
            $role->permissions()->detach();
            $perms = $request->get('permissions');
            $role->syncPermissions($perms);

            // Update updated_at column
            $role->touch();
        }
        if($request->has('is_moderated')) {
            $role->is_moderated = $request->get('is_moderated');
        }
        if($request->has('display_name')) {
            $role->display_name = $request->get('display_name');
        }
        if($request->has('description')) {
            $role->description = $request->get('description');
        }
        $role->save();

        return response()->json($role);
    }

    public function resetPassword(Request $request, $id) {
        $user = auth()->user();
        if($user->id != $id && !$user->can('users_roles_write')) {
            return response()->json([
                'error' => __('You do not have the permission to reset user password')
            ], 403);
        }

        $this->validate($request, [
            'password' => 'required|min:6',
        ]);

        try {
            $pUser = User::withoutTrashed()->findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This user does not exist')
            ], 400);
        }

        $password = Hash::make($request->get('password'));

        $pUser->password = $password;

        if($user->id != $id) {
            $pUser->login_attempts = 3;
        }

        $pUser->save();

        return response()->json(null, 204);
    }

    public function confirmPassword(Request $request, $id) {
        $user = auth()->user();
        if($user->id != $id) {
            return response()->json([
                'error' => __('You do not have the permission to confirm an other user\'s password')
            ], 403);
        }

        $this->validate($request, [
            'password' => 'min:6',
        ]);

        try {
            $pUser = User::withoutTrashed()->findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This user does not exist')
            ], 400);
        }

        if($request->has('password')) {
            $password = Hash::make($request->get('password'));
            $pUser->password = $password;
        }
        
        $pUser->login_attempts = null;
        $pUser->save();

        return response()->json(null, 204);
    }

    // PUT

    // DELETE

    public function deleteUser($id) {
        $user = auth()->user();
        if(!$user->can('users_roles_delete')) {
            return response()->json([
                'error' => __('You do not have the permission to delete users')
            ], 403);
        }

        try {
            $delUser = User::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This user does not exist')
            ], 400);
        }

        $delUser->delete();
        return response()->json($delUser, 200);
    }

    public function deleteRole($id) {
        $user = auth()->user();
        if(!$user->can('users_roles_delete')) {
            return response()->json([
                'error' => __('You do not have the permission to delete roles')
            ], 403);
        }

        try {
            $delRole = Role::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This role does not exist')
            ], 400);
        }

        $delRole->delete();
        return response()->json(null, 204);
    }

    public function deleteAvatar() {
        $user = auth()->user();

        try {
            $user = User::findOrFail($user->id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This user does not exist')
            ], 400);
        }

        Storage::delete($user->avatar);
        $user->avatar = null;
        $user->save();
        return response()->json(null, 204);
    }

    // OTHER FUNCTIONS

}
