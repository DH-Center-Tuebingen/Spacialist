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
            $this->middleware('auth')->except('welcome');
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
        } else {
            $preferences = Preference::all();
            $preferenceValues = [];
            foreach($preferences as $p) {
                $preferenceValues[$p->label] = Preference::decodePreference($p->label, json_decode($p->default_value));
            }
        }

        $concepts = ThConcept::getMap();

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

    public function layer()
    {
        $baselayers = AvailableLayer::where('is_overlay', false)->orderBy('id')->get();
        $overlays = AvailableLayer::with('entity_type')->where('is_overlay', true)->orderBy('id')->get();

        return view('settings.editor.layer', ['baselayers' => $baselayers, 'overlays' => $overlays]);
    }

    public function gis()
    {
        $entityLayers = AvailableLayer::with(['entity_type'])
            ->whereNotNull('entity_type_id')
            ->orWhere('type', 'unlinked')
            ->orderBy('id')
            ->get();
        foreach($entityLayers as &$layer) {
            if(isset($layer->entity_type)) {
                $layer->thesaurus_url = $layer->entity_type->thesaurus_url;
            } else {
                unset($layer->thesaurus_url);
            }
            unset($layer->entity_type);
        }
        $entityLayers = json_encode($entityLayers);

        $data = [
            'entityLayers' => $entityLayers
        ];

        return view('tools.gis', $data);
    }

    public function analysis()
    {
        return view('tools.analysis');
    }
}
