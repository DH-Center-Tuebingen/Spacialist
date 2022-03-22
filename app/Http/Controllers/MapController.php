<?php

namespace App\Http\Controllers;

use App\AvailableLayer;
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
        $user = auth()->user();
        if(!$user->can('view_geodata')) {
            return response()->json([
                'error' => __('You do not have the permission to view the geo data')
            ], 403);
        }
        // layers: id => layer
        $layers = AvailableLayer::all()->getDictionary();
        // geoObjects: id => geoO
        $geodata = Geodata::with(['entity'])->get()->getDictionary();

        // Do not load unnecessary attributes
        foreach($geodata as $g) {
            if(isset($g->entity)) {
                $g->entity->setAppends([]);
            }
        }

        return response()->json([
            'layers' => $layers,
            'geodata' => $geodata
        ]);
    }

    // POST

    public function getEpsgByText(Request $request) {
        $srtext = $request->get('srtext');
        $epsg = \DB::table('spatial_ref_sys')
            ->where('srtext', $srtext)
            ->first();
        return response()->json($epsg);
    }

    // PUT

    // PATCH

    public function updateGeometry($id, Request $request) {
        $user = auth()->user();
        if(!$user->can('create_edit_geodata')) {
            return response()->json([
                'error' => __('You do not have the permission to edit geometric data')
            ], 403);
        }
        $this->validate($request, [
            'geometry' => 'required|json',
            'srid' => 'required|integer|exists:spatial_ref_sys,srid'
        ]);

        try {
            $geodata = Geodata::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This geodata does not exist')
            ], 400);
        }
        $geodata->patch($request->get('geometry'), $request->get('srid'), $user);

        return response()->json(null, 204);
    }

    public function changeLayerPositions($id, Request $request) {
        $user = auth()->user();
        if(!$user->can('create_edit_geodata')) {
            return response()->json([
                'error' => __('You do not have the permission to sort layers')
            ], 403);
        }
        $this->validate($request, [
            'neighbor' => 'required|integer|exists:available_layers,id',
        ]);
        $neighborId = $request->get('neighbor');
        try {
            $layer = AvailableLayer::findOrFail($id);
            $neighbor = AvailableLayer::findOrFail($neighborId);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This layer does not exist')
            ], 400);
        }

        $tmpPos = $layer->position;
        $layer->position = $neighbor->position;
        $neighbor->position = $tmpPos;
        $layer->save();
        $neighbor->save();

        return response()->json(null, 204);
    }

    public function moveLayer($id, Request $request) {
        $user = auth()->user();
        if(!$user->can('create_edit_geodata')) {
            return response()->json([
                'error' => __('You do not have the permission to move layers')
            ], 403);
        }
        try {
            $layer = AvailableLayer::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This layer does not exist')
            ], 400);
        }

        $oldPosition = $layer->position;
        $layer->is_overlay = !$layer->is_overlay;
        $layer->position = AvailableLayer::where('is_overlay', $layer->is_overlay)->max('position') + 1;
        $layer->save();
        $oldSiblings = AvailableLayer::where('is_overlay', !$layer->is_overlay)->where('position', '>', $oldPosition)->get();
        foreach($oldSiblings as $s) {
            $s->position = $s->position - 1;
            $s->save();
        }

        $data = [
            'position' => $layer->position,
        ];

        return response()->json($data, 200);
    }

    // DELETE

    public function delete($id) {
        $user = auth()->user();
        if(!$user->can('upload_remove_geodata')) {
            return response()->json([
                'error' => __('You do not have the permission to delete geo data')
            ], 403);
        }
        try {
            $geodata = Geodata::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This geodata does not exist')
            ], 400);
        }
        $geodata->delete();

        return response()->json(null, 204);
    }
}
