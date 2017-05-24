<?php

namespace App\Http\Controllers;
use App\AvailableLayer;
use Illuminate\Http\Request;
use \Log;

class OverlayController extends Controller {
    public $availableGeometryTypes = [
        'Point', 'Linestring', 'Polygon'
    ];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    public function getGeometryTypes() {
        return response()->json($this->availableGeometryTypes);
    }

    public function deleteLayer($id) {
        AvailableLayer::find($id)->delete();
        return response()->json([]);
    }

    public function addLayer(Request $request) {
        $name = $request->get('name');
        $isOverlay = $request->has('is_overlay') && $request->get('is_overlay') == 'true';
        $layer = new AvailableLayer();
        $layer->name = $name;
        $layer->url = '';
        $layer->type = '';
        $layer->is_overlay = $isOverlay;
        $layer->color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
        $layer->save();
        return response()->json([
            'layer' => $layer
        ]);
    }

    public function updateLayer(Request $request) {
        $id = $request->get('id');
        $layer = AvailableLayer::find($id);
        if($request->has('visible') && $request->get('visible') == 'true') {
            if(!$layer->visible) {
                $layers = AvailableLayer::where('is_overlay', '=', false)
                ->where('id', '!=', $layer->id)
                ->get();
                foreach($layers as $l) {
                    $l->visible = false;
                    $l->save();
                }
            }
        }
        foreach($request->except(['id']) as $k => $v) {
            // cast boolean strings
            if($v == 'true') $v = true;
            else if($v == 'false') $v = false;
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
