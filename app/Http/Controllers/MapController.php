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

    public function addGeometry(Request $request) {
        $this->validate($request, [
            'collection' => 'required|json',
            'srid' => 'required|integer'
        ]);

        $objs = Geodata::createFromFeatureCollection(json_decode($request->get('collection')), $request->get('srid'));
        return response()->json($objs);
    }

    // PUT

    // PATCH

    public function updateGeometry($id, Request $request) {
        $this->validate($request, [
            'feature' => 'required|json',
            'srid' => 'required|integer'
        ]);

        try {
            $geodata = Geodata::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This geodata does not exist'
            ]);
        }
        $geodata->updateGeometry(json_decode($request->get('feature')), $request->get('srid'));
    }

    // DELETE

    public function delete($id) {
        try {
            $geodata = Geodata::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This geodata does not exist'
            ]);
        }
        $geodata->delete();
        return response()->json(null, 204);
    }
}
