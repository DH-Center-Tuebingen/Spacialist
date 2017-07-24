<?php

namespace App\Http\Controllers;
use App\AvailableLayer;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use \Log;

class OverlayController extends Controller {
    public $availableGeometryTypes = \App\Geodata::availableGeometryTypes;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    // GET

    public function getOverlays() {
        $user = \Auth::user();
        if(!$user->can('view_geodata')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $layers = \DB::table('available_layers as al')
            ->select('al.*', 'ct.thesaurus_url')
            ->orderBy('position', 'asc')
            ->leftJoin('context_types as ct', 'context_type_id', '=', 'ct.id')
            ->get();
        return response()->json([
            'layers' => $layers
        ]);
    }

    public function getGeometryTypes() {
        return response()->json($this->availableGeometryTypes);
    }

    // POST

    public function addLayer(Request $request) {
        $user = \Auth::user();
        if(!$user->can('create_edit_geodata')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
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
        return response()->json([
            'layer' => $layer
        ]);
    }

    // PATCH

    public function moveUp($id) {
        $user = \Auth::user();
        if(!$user->can('create_edit_geodata')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $layer = AvailableLayer::find($id);
        $layer2 = AvailableLayer::where('position', '=', $layer->position - 1)->first();
        $layer->position--;
        $layer2->position++;
        $layer->save();
        $layer2->save();
        return response()->json([]);
    }

    public function moveDown($id) {
        $user = \Auth::user();
        if(!$user->can('create_edit_geodata')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $layer = AvailableLayer::find($id);
        $layer2 = AvailableLayer::where('position', '=', $layer->position + 1)->first();
        $layer->position++;
        $layer2->position--;
        $layer->save();
        $layer2->save();
        return response()->json([]);
    }

    public function patchLayer(Request $request, $id) {
        $user = \Auth::user();
        if(!$user->can('create_edit_geodata')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $this->validate($request, AvailableLayer::patchRules);
        try {
            $layer = AvailableLayer::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This layer does not exist'
            ]);
        }

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
        foreach($request->intersect(array_keys(AvailableLayer::patchRules)) as $key => $value) {
            // cast boolean strings
            if($value == 'true') $value = true;
            else if($value == 'false') $value = false;
            $layer->{$key} = $value;
        }
        $layer->save();
        return response()->json([]);
    }

    // PUT

    // DELETE

    public function deleteLayer($id) {
        $user = \Auth::user();
        if(!$user->can('upload_remove_geodata')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        AvailableLayer::find($id)->delete();
        return response()->json([]);
    }
}
