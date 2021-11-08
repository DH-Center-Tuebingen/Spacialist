<?php

namespace App\Plugins\Map\Controllers;

use App\AvailableLayer;
use App\Http\Controllers\Controller;
use App\Plugins\Map\App\Geodata;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class MapController extends Controller
{
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

    public function getLayers(Request $request) {
        $user = auth()->user();
        if(!$user->can('view_geodata')) {
            return response()->json([
                'error' => __('You do not have the permission to view layers')
            ], 403);
        }
        $basic = $request->query('basic');
        $dict = $request->query('d');
        $basicOnly = isset($basic);
        $asDict = isset($dict);
        $baselayers = AvailableLayer::where('is_overlay', false)
            ->orderBy('id')
            ->get();
        $overlayQuery = AvailableLayer::with('entity_type')
            ->where('is_overlay', true)
            ->orderBy('id');
        if($basicOnly) {
            $overlayQuery->whereNull('entity_type_id')
                ->where('type', '!=', 'unlinked');
        }
        $overlays = $overlayQuery->get();

        if($asDict) {
            $baselayers = $baselayers->getDictionary();
            $overlays = $overlays->getDictionary();
        }

        return response()->json([
            'baselayers' => $baselayers,
            'overlays' => $overlays
        ]);
    }

    public function getEntityTypeLayers() {
        $user = auth()->user();
        if(!$user->can('view_geodata')) {
            return response()->json([
                'error' => __('You do not have the permission to view layers')
            ], 403);
        }
        $entityLayers = AvailableLayer::with(['entity_type'])
            ->whereNotNull('entity_type_id')
            ->orWhere('type', 'unlinked')
            ->orderBy('id')
            ->get();

        return response()->json($entityLayers);
    }

    public function getLayer($id) {
        $user = auth()->user();
        if(!$user->can('view_geodata')) {
            return response()->json([
                'error' => __('You do not have the permission to view a layer')
            ], 403);
        }
        try {
            $layer = AvailableLayer::with('entity_type')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This layer does not exist')
            ], 400);
        }

        return response()->json($layer);
    }

    public function getGeometriesByLayer($id) {
        $user = auth()->user();
        if(!$user->can('view_geodata')) {
            return response()->json([
                'error' => __('You do not have the permission to view geodata')
            ], 403);
        }

        try {
            $layer = AvailableLayer::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This layer does not exist')
            ], 400);
        }
        $query = Geodata::with(['entity'])->orderBy('id');
        if($layer->type == 'unlinked') {
            $query->doesntHave('entity');
        } else if(isset($layer->entity_type_id)) {
            $query->whereHas('entity', function($q) use ($layer) {
                $q->where('entity_type_id', $layer->entity_type_id);
            });
        }
        $geodata = $query->get();

        // Do not load unnecessary attributes
        foreach($geodata as $g) {
            if(isset($g->entity)) {
                $g->entity->setAppends([]);
            }
        }

        return response()->json($geodata);
    }

    // POST

    // PATCH

    // PUT

    // DELETE
}
