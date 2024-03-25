<?php

namespace App\Http\Controllers;

use App\AttributeTypes\AttributeBase;
use App\EntityType;
use App\Plugin;
use App\Preference;
use App\ThConcept;
use App\User;
use Illuminate\Http\Request;

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
            $this->middleware('auth')->except(['welcome', 'index', 'external']);
        }
        $this->middleware('guest')->only('welcome');
    }

    public function getGlobalData() {
        if(auth()->check()) {
            $preferences = Preference::getUserPreferences(auth()->id());
            $preferenceValues = [];
            foreach($preferences as $k => $p) {
                $preferenceValues[$k] = $p->value;
            }
            $locale = auth()->user()->getLanguage();
        } else {
            $preferenceValues = [];
            $locale = \App::getLocale();
        }

        $sysPrefs = Preference::getPreferences();

        $concepts = ThConcept::getMap($locale);

        $datatypes = AttributeBase::getTypes();
        $addData = [];
        foreach($datatypes as $key => $datatype) {
            if(method_exists($datatype, "addGlobalData")) {
                $addData[$key] = $datatype::addGlobalData();
            }
        }

        $entityTypes = EntityType::with(['sub_entity_types', 'layer', 'attributes'])
            ->orderBy('id')
            ->get();
        $entityTypeMap = $entityTypes->getDictionary();

        return response()->json([
            'system_preferences' => $sysPrefs,
            'preferences' => $preferenceValues,
            'concepts' => $concepts,
            'entityTypes' => $entityTypeMap,
            'datatype_data' => $addData,
            'colorsets' => sp_get_themes(),
            'analysis' => sp_has_analysis(),
        ]);
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
