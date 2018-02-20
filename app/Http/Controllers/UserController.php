<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Role;

class UserController extends Controller
{
    // GET

    // POST

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

    // OTHER FUNCTIONS

}
