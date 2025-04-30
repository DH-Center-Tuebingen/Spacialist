<?php

namespace App\Http\Controllers;

use App\Attribute;
use App\AttributeTypes\AttributeBase;
use App\Bibliography;
use App\Entity;
use App\EntityType;
use App\Permission;
use App\Plugin;
use App\Preference;
use App\Role;
use App\RolePreset;
use App\ThLanguage;
use App\ThConcept;
use App\User;
use App\VersionInfo;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

# Plugins
use \App\Plugins\Map\App\Geodata;

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

    public function getGlobalData() {
        if(auth()->check()) {
            $preferenceValues = Preference::getUserPreferences(auth()->id(), true);
            $locale = auth()->user()->getLanguage();
        } else {
            $preferenceValues = [];
            $locale = App::getLocale();
        }

        $sysPrefsValues = Preference::getPreferences(true);

        $concepts = ThConcept::getMap($locale);
        
        $tags = $this->getTags();
        $version = $this->getVersion();
        $plugins = Plugin::getPluginsWithMetadata();
        $bibliography = Bibliography::orderBy('id')->get();

        $attributes = Attribute::whereNull('parent_id')->withCount('entity_types')->orderBy('id')->get();
        $attributeSelections = $this->getAttributeSelection($attributes);
        $attributeTypes = AttributeBase::getTypes(true);
        
        $users = User::with('roles')->withoutTrashed()->orderBy('id')->get();
        $deletedUsers = User::with('roles')->onlyTrashed()->orderBy('id')->get();
        $roles = Role::with(['permissions', 'derived'])->orderBy('id')->get();
        $permissions = Permission::orderBy('id')->get();
        $presets = RolePreset::all();
        
        $topEntities = Entity::getEntitiesByParent(null, true);
        $geometryTypes = $this->getGeometryTypes();
        
        $datatypes = AttributeBase::getTypes();
        $datatypeData = [];
        foreach($datatypes as $key => $datatype) {
            if(method_exists($datatype, "getGlobalData")) {
                $datatypeData[$key] = $datatype::getGlobalData();
            }
        }
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
    
    private function getGeometryTypes(){
        if(Plugin::isInstalled('Map')) {
            $types = Geodata::getAvailableGeometryTypes();
        } else {
            return [];
        }
    }
    
    private function getVersion(){
        $versionInfo = new VersionInfo();
        return [
            'full' => $versionInfo->getFullRelease(),
            'readable' => $versionInfo->getReadableRelease(),
            'release' => $versionInfo->getRelease(),
            'name' => $versionInfo->getReleaseName(),
            'time' => $versionInfo->getTime()
        ];
    }
    
    private function getTags(){
        $tagObj = Preference::where('label', 'prefs.tag-root')
        ->value('default_value');
        $tagUri = json_decode($tagObj)->uri;
        $tags = DB::select("
            WITH RECURSIVE
            top AS (
                SELECT br.narrower_id as id, c2.concept_url
                FROM th_broaders br
                JOIN th_concept c ON c.id = br.broader_id
                JOIN th_concept c2 ON c2.id = br.narrower_id
                WHERE c.concept_url = '$tagUri'
                UNION
                SELECT br.narrower_id as id, c.concept_url
                FROM top t, th_broaders br
                JOIN th_concept c ON c.id = br.narrower_id
                WHERE t.id = br.broader_id
            )
            SELECT *
            FROM top
            ORDER BY id
        ");
        return $tags;
    }
    
    private function getAttributeSelection($attributes) {
        $selections = [];
        foreach($attributes as $a) {
            $selection = $a->getSelection();
            if(isset($selection)) {
                // Workaround to check if it is a plain array or a assoc array (table columns)
                // if assoc array, add each entry to their corresponding id
                if(!isset($selection[0])) {
                    foreach($selection as $id => $sel) {
                        $selections[$id] = $sel;
                    }
                } else {
                    $selections[$a->id] = $selection;
                }
            }

            if($a->datatype == 'table') {
                $a->columns = Attribute::where('parent_id', $a->id)->get()->keyBy('id');
            }
        }
        
        return $selections;
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
