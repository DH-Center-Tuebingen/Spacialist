<?php

namespace App\Http\Controllers;

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
            $this->middleware('auth')->except(['welcome', 'index']);
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

        $entityTypes = EntityType::with(['sub_entity_types', 'layer', 'attributes'])
            ->orderBy('id')
            ->get();
        $entityTypeMap = $entityTypes->getDictionary();

        $users = User::all();

        return response()->json([
            'system_preferences' => $sysPrefs,
            'preferences' => $preferenceValues,
            'concepts' => $concepts,
            'entityTypes' => $entityTypeMap,
            'users' => $users
        ]);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
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
