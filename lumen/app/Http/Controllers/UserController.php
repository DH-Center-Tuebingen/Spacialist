<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;

class UserController extends Controller
{
    /**
     * @var \Tymon\JWTAuth\JWTAuth
     */
    protected $jwt;

    public function __construct(JWTAuth $jwt) {
        $this->jwt = $jwt;
    }

    public function get() {
        $role = 'map_user';
        $user = User::find(1);
        if(!$user->hasRole($role)) {
            return response([
                'error' => 'You are not a member of the role \'' . $role . '\''
            ], 409);
        }
        if(!$user->can('view_users')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $user = \Auth::user();
        $roles = \DB::table('user_roles')
            ->join('roles', 'roles.id', '=' , 'role_id')
            ->where('user_id', '=', $user->id)
            ->select('name', 'code')
            ->get();
        return response()->json([
            'user' => $user,
            'roles' => $roles
        ]);
    }

    public function login(Request $request) {
        $this->validate($request, [
            'email'    => 'required|email|max:255',
            'password' => 'required',
        ]);

        try {
            if(!$token = $this->jwt->attempt($request->only('email', 'password'))) {
                return response()->json(['error' => 'user_not_found'], 404);
            }
        } catch(\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['error' => 'token_expired'], 500);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['error' => 'token_invalid'], 500);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'token_absent ' . $e->getMessage()], 500);
        }
        return response()->json(compact('token'));
    }
    public function getAuthenticatedUser(Request $request) {
        try {
            if(!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'user_not_found'], 404);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['error' => 'token_expired'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['error' => 'token_invalid'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'token_absent'], $e->getStatusCode());
        }
        return response()->json(compact('user'));
    }
}
