<?php

namespace App\Http\Controllers;

use App\EntityType;
use App\Preference;
use App\ThConcept;
use App\User;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
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

    public function getPlugins(Request $request) {
        $plugins = [];

        if($request->query('installed') == 1) {
            $plugins = Plugin::whereNotNull('installed_at')->get();
        } else if($request->query('uninstalled') == 1) {
            $plugins = Plugin::whereNull('installed_at')->get();
        } else {
            $pluginPath = base_path('app/Plugins');
            $availablePlugins = File::directories($pluginPath);
            foreach($availablePlugins as $ap) {
                // info(Str::afterLast($ap, '/'));
                $path = Str::finish($ap, '/') . 'data/info.xml';
                if(!File::isFile($path)) continue;

                $xmlString = file_get_contents($path);
                $xmlObject = simplexml_load_string($xmlString);

                $plugins[] = json_decode(json_encode($xmlObject), true);
            }
        }

        return response()->json([
            'plugins' => $plugins,
        ]);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function welcome() {
        return view('welcome');
    }

    /**
     * Show the landing page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        return view('home');
    }
}
