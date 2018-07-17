<?php

namespace App\Http\Controllers;

use App\Permission;
use App\Role;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        $user = User::find(auth()->user()->id);

        return response()->json([
                'status' => 'success',
                'data' => $user
            ]);
    }

    public function getUsers() {
        $users = User::with('roles')->orderBy('id')->get();
        $roles = Role::orderBy('id')->get();

        return response()->json([
            'users' => $users,
            'roles' => $roles
        ]);
    }

    public function getRoles() {
        $roles = Role::with('permissions')->orderBy('id')->get();
        $perms = Permission::orderBy('id')->get();

        return response()->json([
            'roles' => $roles,
            'permissions' => $perms
        ]);
    }

    // POST

    public function login(Request $request) {
        $this->validate($request, [
            'email' => 'required|email|max:255||exists:users,email',
            'password' => 'required'
        ]);

        $credentials = request(['email', 'password']);

        if(!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Invalid Credentials'], 400);
        }

        return response()
            ->json(null, 200)
            ->header('Authorization', $token);
    }

    public function addUser(Request $request) {
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

        return response()->json($user);
    }

    public function addRole(Request $request) {
        $role = new Role();
        foreach($request->only(array_keys(Role::rules)) as $key => $value) {
            $role->{$key} = $value;
        }
        $role->save();
        return response()->json($role);
    }

    // PATCH

    public function setRoles(Request $request, $id) {
        $this->validate($request, [
            'roles' => 'required'
        ]);

        try {
            $user = User::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This user does not exist'
            ], 400);
        }
        $user->detachRoles($user->roles);
        $roles = json_decode($request->get('roles'));
        foreach($roles as $roleId) {
            $user->attachRole($roleId);
        }

        return response()->json(null, 204);
    }

    public function setPermissions(Request $request, $id) {
        $this->validate($request, [
            'permissions' => 'required'
        ]);

        try {
            $role = Role::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This role does not exist'
            ], 400);
        }
        $role->detachPermissions($role->permissions);
        $perms = json_decode($request->get('permissions'));
        foreach($perms as $permId) {
            $role->attachPermission($permId);
        }

        return response()->json(null, 204);
    }

    // PUT

    // DELETE

    public function deleteUser($id) {
        User::find($id)->delete();
        return response()->json(null, 204);
    }

    public function deleteRole($id) {
        Role::find($id)->delete();
        return response()->json(null, 204);
    }

    // OTHER FUNCTIONS

}
