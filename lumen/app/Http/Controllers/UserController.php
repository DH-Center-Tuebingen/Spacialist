<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use DB;
use Tymon\JWTAuth\JWTAuth;
use Zizaco\Entrust;

class UserController extends Controller
{
    /*
    Entrust::hasRole($role);
    Entrust::can('view_concepts');
     */
    /**
     * @var \Tymon\JWTAuth\JWTAuth
     */
    protected $jwt;

    public function __construct(JWTAuth $jwt) {
        $this->jwt = $jwt;
    }

    public function getUserPermissions() {
        $user = \Auth::user();
        return response()->json(
            $this->getUserPermissionsById($user->id)
        );
    }

    public function addRoleToUser(Request $request) {
        $user = \Auth::user();
        if(!$user->can('add_remove_role')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $role = $request->get('role');
        $roleId = DB::table('roles')->where('name', '=', $role)->value('id');
        $receiver = User::find($request->get('user_id'));
        $receiver->attachRole($roleId);
        return response()->json([
            'user' => $receiver,
            'role' => $roleId
        ]);
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

    public function get() {
        $user = \Auth::user();
        $permissions = $this->getUserPermissionsById($user->id);
        return response()->json([
            'user' => $user,
            'permissions' => $permissions
        ]);
    }

    public function login(Request $request) {
        $this->validate($request, [
            'email'    => 'required|email|max:255',
            'password' => 'required',
        ]);

        $valid = $this->validateRequest($request->only('email', 'password'));
        if($valid['status'] == 200) {
            return response()->json($valid['token']);
        } else {
            return response()->json($valid, $valid['status']);
        }
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
