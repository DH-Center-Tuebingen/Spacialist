<?php

namespace App\Http\Controllers;

use App\Role;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    // GET

    // POST

    public function addUser(Request $request) {
        $this->validate($request, [
            'email' => 'required|email|max:255',
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

    // PUT

    // DELETE

    public function deleteUser($id) {
        User::find($id)->delete();
        return response()->json();
    }

    public function deleteRole($id) {
        Role::find($id)->delete();
        return response()->json();
    }

    // OTHER FUNCTIONS

}
