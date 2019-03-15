<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Attribute;
use App\AvailableLayer;
use App\Bibliography;
use App\Entity;
use App\EntityType;
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
            $preferences = Preference::all();
            $preferenceValues = [];
            foreach($preferences as $p) {
                $preferenceValues[$p->label] = Preference::decodePreference($p->label, json_decode($p->default_value));
            }
            $locale = \App::getLocale();
        }

        $concepts = ThConcept::getMap($locale);

        $entityTypes = EntityType::with(['sub_entity_types', 'layer'])
            ->orderBy('id')
            ->get();
        $entityTypeMap = $entityTypes->getDictionary();

        return response()->json([
            'preferences' => $preferenceValues,
            'concepts' => $concepts,
            'entityTypes' => $entityTypeMap
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
