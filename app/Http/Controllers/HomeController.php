<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Attribute;
use App\AvailableLayer;
use App\Bibliography;
use App\Context;
use App\ContextType;
use App\Permission;
use App\Preference;
use App\Role;
use App\ThConcept;
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
        $userPrefs = Preference::getUserPreferences($id);
        return view('settings.userpreferences', ['preferences' => json_encode($userPrefs)]);
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
        $attributes = Attribute::all();
        $contextTypes = ContextType::all();
        return view('settings.editor.dme', ['attributes' => $attributes, 'contextTypes' => $contextTypes]);
    }

    public function layer()
    {
        $baselayers = AvailableLayer::where('is_overlay', false)->get();
        $overlays = AvailableLayer::with('context_type')->where('is_overlay', true)->get();
        return view('settings.editor.layer', ['baselayers' => $baselayers, 'overlays' => $overlays]);
    }

    public function gis()
    {
        return view('tools.gis');
    }

    public function bibliography()
    {
        $entries = Bibliography::all();
        return view('tools.bibliography', ['entries' => $entries]);
    }

    public function analysis()
    {
        return view('tools.analysis');
    }
}
