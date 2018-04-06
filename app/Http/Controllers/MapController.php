<?php

namespace App\Http\Controllers;

use App\AvailableLayer;
use App\Context;
use App\ContextType;
use App\Geodata;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class MapController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    // GET

    public function getData() {
        // layers: id => layer
        $layers = AvailableLayer::all()->getDictionary();
        // contextTypes: id => contextType
        $contextTypes = ContextType::all()->getDictionary();
        // contexts: id => context
        $contexts = Context::all()->getDictionary();
        // geoObjects: id => geoO
        $geodata = Geodata::with(['context'])->get()->getDictionary();

        return response()->json([
            'layers' => $layers,
            'contextTypes' => $contextTypes,
            'contexts' => $contexts,
            'geodata' => $geodata
        ]);
    }

    // POST

    // PUT

    // DELETE

}
