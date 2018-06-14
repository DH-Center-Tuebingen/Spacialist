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
        parent::__construct();
        if(!Preference::hasPublicAccess()) {
            $this->middleware('auth')->except('welcome');
        }
        $this->middleware('guest')->only('welcome');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $contextTypes = ContextType::with('sub_context_types')
            ->orderBy('id')
            ->get();
        $contextTypeMap = $contextTypes->getDictionary();
        $contextTypeMap = json_encode($contextTypeMap);

        $roots = Context::getEntitiesByParent();

        $bibliography = Bibliography::all();

        $data = [
            'bibliography' => $bibliography,
            'contextTypes' => $contextTypeMap,
            'roots' => $roots
        ];
        return view('home', $data);
    }

    public function welcome() {
        return view('welcome');
    }

    public function prefs()
    {
        $preferences = Preference::getPreferences();
        return view('settings.preferences', ["preferences" => json_encode($preferences)]);
    }

    public function userPrefs($id)
    {
        $userPrefs = Preference::getUserPreferences($id);
        return view('settings.userpreferences', ['preferences' => json_encode($userPrefs), 'user_id' => $id]);
    }

    public function users()
    {
        $users = User::with('roles')->orderBy('id')->get();
        $roles = Role::orderBy('id')->get();
        return view('settings.users', ['users' => $users, 'roles' => $roles]);
    }

    public function roles()
    {
        $roles = Role::with('permissions')->orderBy('id')->get();
        $perms = Permission::orderBy('id')->get();
        return view('settings.roles', ['roles' => $roles, 'permissions' => $perms]);
    }

    public function dme()
    {
        $attributes = Attribute::whereNull('parent_id')->orderBy('id')->get();
        foreach($attributes as $a) {
            $a->columns = Attribute::where('parent_id', $a->id)->get();
        }
        $contextTypes = ContextType::with('sub_context_types')
            ->orderBy('id')
            ->get();

        return view('settings.editor.dme', ['attributes' => $attributes, 'contextTypes' => $contextTypes]);
    }

    public function layer()
    {
        $baselayers = AvailableLayer::where('is_overlay', false)->orderBy('id')->get();
        $overlays = AvailableLayer::with('context_type')->where('is_overlay', true)->orderBy('id')->get();

        return view('settings.editor.layer', ['baselayers' => $baselayers, 'overlays' => $overlays]);
    }

    public function gis()
    {
        $contextTypes = ContextType::with('sub_context_types')
            ->orderBy('id')
            ->get();
        $contextTypeMap = $contextTypes->getDictionary();
        $contextTypeMap = json_encode($contextTypeMap);

        $contextLayers = AvailableLayer::with(['context_type'])
            ->whereNotNull('context_type_id')
            ->orWhere('type', 'unlinked')
            ->orderBy('id')
            ->get();
        foreach($contextLayers as &$layer) {
            if(isset($layer->context_type)) {
                $layer->thesaurus_url = $layer->context_type->thesaurus_url;
            } else {
                unset($layer->thesaurus_url);
            }
            unset($layer->context_type);
        }
        $contextLayers = json_encode($contextLayers);

        $data = [
            'contextTypes' => $contextTypeMap,
            'contextLayers' => $contextLayers
        ];

        return view('tools.gis', $data);
    }

    public function bibliography()
    {
        $entries = Bibliography::orderBy('id')->get();
        return view('tools.bibliography', ['entries' => $entries]);
    }

    public function analysis()
    {
        return view('tools.analysis');
    }
}
