<?php

namespace App\Http\Controllers;

use App\AccessRule;
use App\Entity;
use App\File;
use App\Geodata;
use App\Group;
use App\Permission;
use App\Role;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        $user = User::with(['roles', 'groups'])->find(auth()->user()->id);
        $user->setPermissions();

        return response()->json([
            'status' => 'success',
            'data' => $user
        ]);
    }

    public function getUsers() {
        $user = auth()->user();
        if(!$user->can('view_users')) {
            return response()->json([
                'error' => __('You do not have the permission to view users')
            ], 403);
        }
        $users = User::with(['roles', 'groups'])->orderBy('id')->get();
        $roles = Role::orderBy('id')->get();
        $groups = Group::orderBy('id')->get();

        return response()->json([
            'users' => $users,
            'roles' => $roles,
            'groups' => $groups
        ]);
    }

    public function getRoles() {
        $user = auth()->user();
        if(!$user->can('view_users')) {
            return response()->json([
                'error' => __('You do not have the permission to view roles')
            ], 403);
        }
        $roles = Role::with('permissions')->orderBy('id')->get();
        $perms = Permission::orderBy('id')->get();

        return response()->json([
            'roles' => $roles,
            'permissions' => $perms
        ]);
    }

    public function getGroups() {
        $user = auth()->user();
        if(!$user->can('view_users')) {
            return response()->json([
                'error' => __('You do not have the permission to view groups')
            ], 403);
        }
        $groups = Group::orderBy('id')->get();

        return response()->json([
            'groups' => $groups,
        ]);
    }

    public function getAccessRules($id, $type) {
        $user = auth()->user();
        if(!$user->can('view_concepts')) {
            return response()->json([
                'error' => __('You do not have the permission to get access rules')
            ], 403);
        }

        $model = $this->getAccessRuleSupportedModel($id, $type);
        if(!isset($model)) {
            return response()->json([
                'error' => __('This type does not support access restrictions')
            ], 400);
        }

        return response()->json([
            'rules' => $model->access_rules
        ]);
    }

    // POST

    public function login(Request $request) {
        $this->validate($request, [
            'email' => 'required|email|max:255|exists:users,email',
            'password' => 'required'
        ]);

        $credentials = request(['email', 'password']);

        if(!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => __('Invalid Credentials')], 400);
        }

        return response()
            ->json(null, 200)
            ->header('Authorization', $token);
    }

    public function addUser(Request $request) {
        $user = auth()->user();
        if(!$user->can('create_users')) {
            return response()->json([
                'error' => __('You do not have the permission to add new users')
            ], 403);
        }
        $this->validate($request, [
            'email' => 'required|email|max:255|unique:users,email',
            'name' => 'required|string|max:255',
            'password' => 'required',
        ]);

        $name = $request->get('name');
        $email = $request->get('email');
        $password = Hash::make($request->get('password'));

        $user = new User();
        $user->name = $name;
        $user->email = Str::lower($email);
        $user->password = $password;
        $user->save();
        $user = User::find($user->id);

        return response()->json($user);
    }

    public function addRole(Request $request) {
        $user = auth()->user();
        if(!$user->can('add_edit_role')) {
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
        $role->save();
        $role = Role::find($role->id);
        return response()->json($role);
    }

    public function addGroup(Request $request) {
        $user = auth()->user();
        if(!$user->can('add_edit_group')) {
            return response()->json([
                'error' => __('You do not have the permission to add groups')
            ], 403);
        }
        $this->validate($request, Group::rules);

        $group = new Group();
        foreach($request->only(array_keys(Group::rules)) as $key => $value) {
            $group->{$key} = $value;
        }
        $group->save();
        $group = Group::find($group->id);
        return response()->json($group);
    }

    public function logout(Request $request) {
        auth()->logout(true);
        auth()->invalidate(true);
    }

    // PATCH

    public function patchUser(Request $request, $id) {
        $user = auth()->user();

        if(!$user->can('add_remove_role')) {
            return response()->json([
                'error' => __('You do not have the permission to set user roles')
            ], 403);
        }
        $this->validate($request, [
            'roles' => 'array',
            'groups' => 'array',
            'email' => 'email'
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
        if($request->has('groups')) {
            $user->groups()->detach();
            $groups = $request->get('groups');
            $user->groups()->attach($groups);

            // Update updated_at column
            $user->touch();
        }
        if($request->has('email')) {
            $user->email = $request->get('email');
            $user->save();
        }

        // return user without roles relation
        $user->unsetRelation('roles');
        // return user without groups relation
        $user->unsetRelation('groups');

        return response()->json($user);
    }

    public function patchRole(Request $request, $id) {
        $user = auth()->user();
        if(!$user->can('add_remove_permission')) {
            return response()->json([
                'error' => __('You do not have the permission to set role permissions')
            ], 403);
        }
        $this->validate($request, Role::patchRules);

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
        if($request->has('display_name')) {
            $role->display_name = $request->get('display_name');
        }
        if($request->has('description')) {
            $role->description = $request->get('description');
        }
        $role->save();

        return response()->json($role);
    }

    public function patchGroup(Request $request, $id) {
        $user = auth()->user();
        if(!$user->can('add_edit_group')) {
            return response()->json([
                'error' => __('You do not have the permission to edit groups')
            ], 403);
        }
        $this->validate($request, Group::patchRules);

        if(!$this->hasInput($request)) {
            return response()->json(null, 204);
        }

        try {
            $group = Group::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This group does not exist')
            ], 400);
        }

        if($request->has('display_name')) {
            $group->display_name = $request->get('display_name');
        }
        if($request->has('description')) {
            $group->description = $request->get('description');
        }
        $group->save();

        return response()->json($group);
    }

    public function restrictResourceAccess(Request $request, $gid) {
        $user = auth()->user();
        // TODO which permission?
        if(!$user->can('add_edit_group')) {
            return response()->json([
                'error' => __('You do not have the permission to edit groups')
            ], 403);
        }
        $this->validate($request, [
            'file_id' => 'required_without_all:entity_id,geodata_id|integer|exists:files,id',
            'entity_id' => 'required_without_all:file_id,geodata_id|integer|exists:entities,id',
            'geodata_id' => 'required_without_all:entity_id,file_id|integer|exists:geodata,id',
            'write_access' => 'required|boolean'
        ]);

        $writeAccess = $request->get('write_access');

        try {
            Group::findOrFail($gid);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This group does not exist')
            ], 400);
        }

        $model;
        if($request->has('file_id')) {
            $fid = $request->get('file_id');
            try {
                $model = File::findOrFail($fid);
            } catch(ModelNotFoundException $e) {
                return response()->json([
                    'error' => __('This file does not exist')
                ], 400);
            }
        }
        if($request->has('entity_id')) {
            $eid = $request->get('entity_id');
            try {
                $model = Entity::findOrFail($eid);
            } catch(ModelNotFoundException $e) {
                return response()->json([
                    'error' => __('This entity does not exist')
                ], 400);
            }
        }
        if($request->has('geodata_id')) {
            $geoid = $request->get('geodata_id');
            try {
                $model = Geodata::findOrFail($geoid);
            } catch(ModelNotFoundException $e) {
                return response()->json([
                    'error' => __('This geodata does not exist')
                ], 400);
            }
        }
        if(!$writeAccess) {
            // If no rules exist and new rule has no write access, abort
            // adding rule
            // otherwise the model would be locked "forever"
            $ruleCount = AccessRule::where('objectable_id', $model->id)
            ->where('objectable_type', $model->getMorphClass())
            ->count();
            if($ruleCount === 0) {
                return response()->json([
                    'error' => __('First group restriction must have write access')
                ], 400);
            }
        }
        $ruleExists = AccessRule::where('objectable_id', $model->id)
            ->where('objectable_type', $model->getMorphClass())
            ->where('group_id', $gid)
            ->exists();
        if($ruleExists) {
            return response()->json([
                'error' => __('This group restriction already exists')
            ], 400);
        }
        Entity::userHasWriteAccess($model);

        // If target is an entity, the group must have at least
        // read access to it's parent element
        if($model->getMorphClass() == 'entities') {
            // If target has no root entity (thus is a root entity itself)
            // we can safely set access rules
            if(isset($model->root_entity_id)) {
                $rootEntity = Entity::find($model->root_entity_id);
                Entity::userHasReadAccess($rootEntity, [
                    'groups' => [$gid]
                ]);
            }
        }

        $ar = new AccessRule();
        $ar->objectable_id = $model->id;
        $ar->objectable_type = $model->getMorphClass();
        $ar->group_id = $gid;
        $ar->rules = $writeAccess ? 'rw' : 'r';
        $ar->save();

        return response()->json($ar);
    }

    // PUT

    // DELETE

    public function deleteUser($id) {
        $user = auth()->user();
        if(!$user->can('delete_users')) {
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
        return response()->json(null, 204);
    }

    public function deleteRole($id) {
        $user = auth()->user();
        if(!$user->can('view_concepts')) {
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

    public function deleteGroup($id) {
        $user = auth()->user();
        if(!$user->can('delete_group')) {
            return response()->json([
                'error' => __('You do not have the permission to delete groups')
            ], 403);
        }

        try {
            $delGrp = Group::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This group does not exist')
            ], 400);
        }

        $delGrp->delete();
        return response()->json(null, 204);
    }

    public function removeResourceAccessRestriction($gid, $mid, $type) {
        $user = auth()->user();
        if(!$user->can('add_edit_group')) {
            return response()->json([
                'error' => __('You do not have the permission to delete groups')
            ], 403);
        }

        try {
            Group::findOrFail($gid);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This group does not exist')
            ], 400);
        }

        $model = $this->getAccessRuleSupportedModel($mid, $type);
        if(!isset($model)) {
            return response()->json([
                'error' => __('This type does not support access restrictions')
            ], 400);
        }
        Entity::userHasWriteAccess($model);

        AccessRule::where('objectable_id', $model->id)
            ->where('objectable_type', $model->getMorphClass())
            ->where('group_id', $gid)
            ->delete();

        return response()->json(null, 204);
    }

    // OTHER FUNCTIONS

    private function getAccessRuleSupportedModel($mid, $type) {
        $model = null;

        switch($type) {
            case 'file':
                try {
                    $model = File::findOrFail($mid);
                } catch(ModelNotFoundException $e) {
                    return response()->json([
                        'error' => __('This file does not exist')
                    ], 400);
                }
                break;
            case 'entity':
                try {
                    $model = Entity::findOrFail($mid);
                } catch(ModelNotFoundException $e) {
                    return response()->json([
                        'error' => __('This entity does not exist')
                    ], 400);
                }
                break;
            case 'geodata':
                try {
                    $model = Geodata::findOrFail($mid);
                } catch(ModelNotFoundException $e) {
                    return response()->json([
                        'error' => __('This geodata does not exist')
                    ], 400);
                }
                break;
        }

        return $model;
    }
}
