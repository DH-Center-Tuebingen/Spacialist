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

    public function addLayer(Request $request) {
        $this->validate($request, [
            'name' => 'required|string',
            'is_overlay' => 'nullable|boolean_string'
        ]);

        $name = $request->get('name');
        $isOverlay = $request->has('is_overlay') && $request->get('is_overlay') == 'true';
        $layer = new AvailableLayer();
        $layer->name = $name;
        $layer->url = '';
        $layer->type = '';
        $layer->is_overlay = $isOverlay;
        $layer->color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
        $layer->save();

        return response()->json($layer);
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

    public function updateLayer($id, Request $request) {
        $this->validate($request, AvailableLayer::patchRules);
        try {
            $layer = AvailableLayer::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This layer does not exist'
            ]);
        }

        // If updated baselayer's visibility is set to true, set all other base layer's visibility to false
        if(!$layer->is_overlay && !$layer->visible && $request->has('visible') && $request->get('visible') == 'true') {
            $layers = AvailableLayer::where('is_overlay', '=', false)
                ->where('id', '!=', $layer->id)
                ->where('visible', true)
                ->get();
            foreach($layers as $l) {
                $l->visible = false;
                $l->save();
            }
        }
        foreach($request->only(array_keys(AvailableLayer::patchRules)) as $key => $value) {
            // cast boolean strings
            if($value == 'true') $value = true;
            else if($value == 'false') $value = false;
            $layer->{$key} = $value;
        }
        $layer->save();
        return response()->json(null, 204);
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
