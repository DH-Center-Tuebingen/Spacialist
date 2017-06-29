<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\User;
use App\Role;
use App\Permission;
use DB;
use Tymon\JWTAuth\JWTAuth;
use Zizaco\Entrust;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * @var \Tymon\JWTAuth\JWTAuth
     */
    protected $jwt;

    public function __construct(JWTAuth $jwt) {
        $this->jwt = $jwt;
    }

    // GET

    public function getUsers() {
        $user = \Auth::user();
        if(!$user->can('view_users')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        return response()->json([
            'users' => User::all()
        ]);
    }

    public function getActiveUser() {
        $user = \Auth::user();
        $permissions = $this->getUserPermissionsById($user->id);
        return response()->json([
            'user' => $user,
            'permissions' => $permissions
        ]);
    }

    public function getRoles() {
        $user = \Auth::user();
        if(!$user->can('add_remove_role') && !$user->can('add_edit_role')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        return response()->json([
            'roles' => Role::all(),
            'permissions' => Permission::all()
        ]);
    }

    public function getRolesByUser($id) {
        $user = \Auth::user();
        if(!$user->can('add_remove_role')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        return response()->json([
            'roles' => DB::table('role_user as ru')
                ->select('r.id', 'r.name', 'r.display_name', 'r.description')
                ->join('roles as r', 'ru.role_id', '=', 'r.id')
                ->where('ru.user_id', '=', $id)
                ->get()
        ]);
    }

    public function getPermissionsByRole($id) {
        $user = \Auth::user();
        if(!$user->can('add_remove_permission')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        return response()->json([
            'permissions' => DB::table('permissions as p')
                                ->select('p.*')
                                ->join('permission_role as pr', 'pr.permission_id', '=', 'p.id')
                                ->where('pr.role_id', '=', $id)
                                ->get()
        ]);
    }

    // POST

    public function login(Request $request) {
        $this->validate($request, [
            'email'    => 'required|email|max:255',
            'password' => 'required'
        ]);

        $request->email = Str::lower($request->email);

        $valid = $this->validateRequest($request->only('email', 'password'));
        if($valid['status'] == 200) {
            return response()->json($valid['token']);
        } else {
            return response()->json($valid, $valid['status']);
        }
    }

    public function add(Request $request) {
        $this->validate($request, [
            'email' => 'required|email|max:255',
            'name' => 'required|alpha_dash|max:255',
            'password' => 'required',
        ]);

        $user = \Auth::user();
        if(!$user->can('create_users')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $name = $request->get('name');
        $email = $request->get('email');
        $password = Hash::make($request->get('password'));

        $newUser = new User();
        $newUser->name = $name;
        $newUser->email = Str::lower($email);
        $newUser->password = $password;
        $newUser->save();

        return response()->json([
            'user' => $newUser
        ]);
    }

    // PATCH

    public function patch(Request $request, $id) {
        // TODO variable keys

        $user = \Auth::user();
        if(!$user->can('change_password')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $editedUser = User::find($id);
        //$keys = ['name', 'email', 'password'];
        $keys = ['password']; //currently only password is supported
        $updated = false;
        foreach($keys as $key) {
            if($request->has($key)) {
                $value = $request->get($key);
                if($key == 'password') $value = Hash::make($value);
                $editedUser->{$key} = $value;
                $updated = true;
            }
        }
        if($updated) $editedUser->save();
        return response()->json([
            'user' => $editedUser
        ]);
    }

    public function patchRole(Request $request, $name) {
        // TODO variable keys

        $user = \Auth::user();
        if(!$user->can('add_edit_role')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $editedRole = Role::where('name', $name)->first();
        $keys = ['name', 'display_name', 'description'];
        foreach($keys as $key) {
            if($request->has($key)) {
                $value = $request->get($key);
                $editedRole->{$key} = $value;
            }
        }
        $editedRole->save();
        return response()->json([
            'role' => $editedRole
        ]);
    }

    public function addRoleToUser(Request $request, $id) {
        $this->validate($request, [
            'role_id' => 'required|integer'
        ]);

        $user = \Auth::user();
        if(!$user->can('add_remove_role')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $role_id = $request->get('role_id');
        $selectedUser = User::find($id);
        $selectedUser->attachRole($role_id);
    }

    public function removeRoleFromUser(Request $request, $id) {
        $this->validate($request, [
            'role_id' => 'required|integer'
        ]);

        $user = \Auth::user();
        if(!$user->can('add_remove_role')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $role_id = $request->get('role_id');
        $selectedUser = User::find($id);
        $selectedUser->detachRole($role_id);
    }

    // PUT

    public function putRole(Request $request, $name) {
        // TODO variable keys

        $user = \Auth::user();
        if(!$user->can('add_edit_role')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $role = Role::where('name', $name)->first();
        if ($role === null) {
            $role = new Role();
        }
        $keys = ['name', 'display_name', 'description'];
        foreach($keys as $key) {
            if($request->has($key)) {
                $value = $request->get($key);
                $role->{$key} = $value;
            }
        }
        $role->save();
        return response()->json([
            'role' => $role
        ]);
    }

    public function putRolePermission($rid, $pid) {
        $user = \Auth::user();
        if(!$user->can('add_remove_permission')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $permission_role = DB::table('permission_role')->where([
            ['role_id', $rid],
            ['permission_id', $pid]
            ])->first();
            if ($permission_role === null){
                DB::table('permission_role')
                ->insert([
                    'role_id' => $rid,
                    'permission_id' => $pid
                ]);
            }
            return response()->json();
        }

    // DELETE

    public function delete($id) {
        $user = \Auth::user();
        if(!$user->can('delete_users')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $toDelete = User::find($id);
        $toDelete->delete();
    }

    public function deleteRole($id) {
        $user = \Auth::user();
        if(!$user->can('delete_role')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        Role::find($id)->delete();
        return response()->json();
    }

    public function removeRolePermission($rid, $pid) {
        $user = \Auth::user();
        if(!$user->can('add_remove_permission')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        DB::table('permission_role')
        ->where([
            ['role_id', '=', $rid],
            ['permission_id', '=', $pid]
        ])
        ->delete();
        return response()->json();
    }

    // OTHER FUNCTIONS

    public function getUserPermissions() {
        $user = \Auth::user();
        return response()->json(
            $this->getUserPermissionsById($user->id)
        );
    }

    private function getUserPermissionsById($id) {
        $permissions = DB::table('role_user as ru')
            ->select('p.name')
            ->join('permission_role as pr', 'ru.role_id', '=', 'pr.role_id')
            ->join('permissions as p', 'p.id', '=', 'pr.permission_id')
            ->orderBy('id')
            ->groupBy('id')
            ->where('user_id', '=', $id)
            ->get();
        $hash = [];
        foreach($permissions as $p) {
            $hash[$p->name] = '1';
        }
        return $hash;
    }

    private function validateRequest($request) {
        try {
            if(!$token = $this->jwt->attempt($request)) {
                return [
                    'error' => 'user_not_found',
                    'status' => 404
                ];
            }
        } catch(\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return [
                'error' => 'token_expired',
                'status' => 500
            ];
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return [
                'error' => 'token_invalid',
                'status' => 500
            ];
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return [
                'error' => 'token_absent',
                'status' => 500
            ];
        }
        return [
            'error' => '',
            'status' => 200,
            'token' => compact('token')
        ];
    }

    private function validateToken() {
        try {
            if(!$user = JWTAuth::parseToken()->authenticate()) {
                return [
                    'error' => 'user_not_found',
                    'status' => 404
                ];
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return [
                'error' => 'token_expired',
                'status' => 500
            ];
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return [
                'error' => 'token_invalid',
                'status' => 500
            ];
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            return [
                'error' => 'token_absent',
                'status' => 500
            ];
        }
        return [
            'user' => $user,
            'status' => 200
        ];
    }
}
