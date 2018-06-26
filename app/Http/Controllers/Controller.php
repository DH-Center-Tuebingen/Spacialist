<?php

namespace App\Http\Controllers;

use App\ContextType;
use App\Preference;
use App\ThConcept;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\View;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct() {
        $preferences = Preference::all();
        $preferenceValues = [];
        foreach($preferences as $p) {
            $preferenceValues[$p->label] = Preference::decodePreference($p->label, json_decode($p->default_value));
        }

        $concepts = json_encode(ThConcept::getMap());

        $contextTypes = ContextType::with('sub_context_types')
            ->orderBy('id')
            ->get();
        $contextTypeMap = $contextTypes->getDictionary();
        $contextTypeMap = json_encode($contextTypeMap);

        View::share('p', $preferenceValues);
        View::share('concepts', $concepts);
        View::share('contextTypes', $contextTypeMap);
  }
}
