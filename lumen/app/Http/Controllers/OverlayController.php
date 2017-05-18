<?php

namespace App\Http\Controllers;
use App\AvailableLayer;
use Illuminate\Http\Request;
use \Log;

class OverlayController extends Controller {
    public $availableGeometryTypes = [
        'Point', 'Linestring', 'Polygon', 'Multipoint', 'Multilinestring', 'Multipolygon'
    ];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    public function deleteLayer($id) {
        AvailableLayer::find($id)->delete();
        return response()->json([]);
    }

    public function updateLayer(Request $request) {
        $id = $request->get('id');
        $layer = AvailableLayer::find($id);
        foreach($request->except(['id']) as $k => $v) {
            if(!$layer->getAttribute($k)) continue;
            $layer->{$k} = $v;
        }
        $layer->save();
        return response()->json([]);
    }

    public function moveUp($id) {
        $layer = AvailableLayer::find($id);
        $layer2 = AvailableLayer::where('position', '=', $layer->position - 1)->first();
        $layer->position--;
        $layer2->position++;
        $layer->save();
        $layer2->save();
        return response()->json([]);
    }

    public function moveDown($id) {
        $layer = AvailableLayer::find($id);
        $layer2 = AvailableLayer::where('position', '=', $layer->position + 1)->first();
        $layer->position++;
        $layer2->position--;
        $layer->save();
        $layer2->save();
        return response()->json([]);
    }

    public function getAll() {
        $layers = \DB::table('available_layers as al')
            ->select('al.*', 'ct.thesaurus_url')
            ->orderBy('position', 'asc')
            ->leftJoin('context_types as ct', 'context_type_id', '=', 'ct.id')
            ->get();
        foreach($layers as $l) {
            $label = ContextController::getLabel($l->thesaurus_url);
            $l->label = $label;
            unset($l->thesaurus_url);
        }
        return response()->json([
            'layers' => $layers
        ]);
    }
}
