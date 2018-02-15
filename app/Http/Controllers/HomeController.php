<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Context;
use App\Permission;
use App\Preference;
use App\Role;
use App\User;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $var = Context::with(['child_contexts', 'context_type'])->whereNull('root_context_id')->take(20)->get();
        // $var = [
        //     [
        //         'name' => 'Testcontext'
        //     ]
        // ];
        foreach($var as $k => $v) {
            if(count($v->child_contexts) === 0) {
                unset($var[$k]->child_contexts);
            }
        }
        return view('home', ['contexts' => $var]);
    }

    public function prefs()
    {
        $preferences = Preference::getPreferences();
        return view('settings.preferences', ["preferences" => json_encode($preferences)]);
    }

    public function userPrefs($id)
    {
        \Log::info("Getting here with $id");
        return view('settings.userpreferences');
    }

    public function users()
    {
        $users = User::with('roles')->get();
        $roles = Role::all();
        return view('settings.users', ['users' => $users, 'roles' => $roles]);
    }

    public function roles()
    {
        $roles = Role::with('permissions')->get();
        $perms = Permission::all();
        return view('settings.roles', ['roles' => $roles, 'permissions' => $perms]);
    }

    public function dme()
    {
        return view('settings.editor.dme');
    }

    public function layer()
    {
        return view('settings.editor.layer');
    }

    public function gis()
    {
        return view('tools.gis');
    }

    public function bibliography()
    {
        return view('tools.bibliography');
    }

    public function analysis()
    {
        return view('tools.analysis');
    }
}
