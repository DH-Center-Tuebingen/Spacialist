<?php

namespace App\Http\Controllers;

use App\Attribute;
use App\AttributeTypes\AttributeBase;
use App\Bibliography;
use App\Entity;
use App\EntityType;
use App\Globals;
use App\Permission;
use App\Plugin;
use App\Preference;
use App\Role;
use App\RolePreset;
use App\ThConcept;
use App\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

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
            $this->middleware('auth:sanctum')->except(['welcome', 'index', 'external']);
        }
        // $this->middleware('guest')->only('welcome');
    }

    public function checkAccesspointAccess(Request $request) {
        $user = auth()->user();

        $accesspoint = Str::finish($request->get('endpoint', '/'), '/');

        if(array_key_exists($accesspoint, $user->accesspoints)) {
            return response()->json(null, 204);
        }

        return response()->json([
            'redirect' => array_keys($user->accesspoints)[0],
        ]);
    }

    public function getGlobalData() {
        if(auth()->check()) {
            $preferenceValues = Preference::getUserPreferences(auth()->id(), true);
            $locale = auth()->user()->getLanguage();
        } else {
            $preferenceValues = [];
            $locale = App::getLocale();
        }

        $accesspoints = [
            "Core" => [
                "name" => "Core",
                "url" => "/",
            ],
        ];

        $sysPrefsValues = Preference::getPreferences(true);

        $concepts = ThConcept::getMap($locale);

        $tags = Globals::getTags();
        $version = Globals::getVersion();
        $plugins = Plugin::getWithMetadata();
        $bibliography = Bibliography::orderBy('id')->get();

        $attributes = Attribute::whereNull('parent_id')->withCount('entity_types')->orderBy('id')->get();
        $attributeSelections = Attribute::getSelectionsFor($attributes);
        $attributeTypes = AttributeBase::getTypes(true);

        $users = User::with('roles')->withoutTrashed()->orderBy('id')->get();
        $deletedUsers = User::with('roles')->onlyTrashed()->orderBy('id')->get();
        $roles = Role::with(['permissions', 'derived'])->orderBy('id')->get();
        $permissions = Permission::orderBy('id')->get();
        $presets = RolePreset::all();

        $topEntities = Entity::getEntitiesByParent(null, true);
        $geometryTypes = Globals::getGeometryTypes();

        $datatypes = AttributeBase::getTypes();
        $datatypeData = [];
        foreach($datatypes as $key => $datatype) {
            if(method_exists($datatype, "getGlobalData")) {
                $datatypeData[$key] = $datatype::getGlobalData();
            }
        }

        $installedPlugins = Plugin::getInstalled();
        foreach($installedPlugins as $plugin) {
            $accesspoints = array_merge($accesspoints, $plugin->getAccessPoints());
        }

        // TODO handle layer relation in Map Plugin
        // $entityTypes = EntityType::with(['sub_entity_types', 'layer', 'attributes'])
        $entityTypes = EntityType::with(['sub_entity_types', 'attributes'])
            ->orderBy('id')
            ->get();
        $entityTypeMap = $entityTypes->getDictionary();

        return response()->json([
            'system_preferences' => $sysPrefsValues,
            'preferences' => $preferenceValues,
            'concepts' => $concepts,
            'entityTypes' => $entityTypeMap,
            'datatype_data' => $datatypeData,
            'accesspoints' => $accesspoints,
            'colorsets' => sp_get_themes(),
            'analysis' => sp_has_analysis(),
            'attributes' => $attributes,
            'attributeSelections' => $attributeSelections,
            'users' => $users,
            'deleted_users' => $deletedUsers,
            'roles' => $roles,
            'permissions' => $permissions,
            'presets' => $presets,
            'topEntities' => $topEntities,
            'bibliography' => $bibliography,
            'tags' => $tags,
            'version' => $version,
            'plugins' => $plugins,
            'geometryTypes' => $geometryTypes,
            'attributeTypes' => $attributeTypes,
        ]);
    }

    public function updateSession() {
        return response()->json();
    }

    public function welcome(Request $request) {
        $activeSite = 'start';
        $siteFromReq = $request->get('s', 'start');
        switch($siteFromReq) {
            case 'about':
                $activeSite = 'about';
                break;
            case 'access':
                $activeSite = 'access';
                break;
        }
        return view('welcome', [
            'site' => $activeSite,
        ]);
    }

    public function external(Request $request) {
        return view('external', [
            'access' => Preference::hasPublicAccess(),
        ]);
    }

    /**
     * Show the landing page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $plugins = Plugin::getInstalled();
        return view('home')
            ->with('plugins', $plugins);
    }
}
