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
            ->orderBy('position')
            ->get();
        $overlayQuery = AvailableLayer::with('entity_type')
            ->where('is_overlay', true)
            ->orderBy('position');
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
            ->orderBy('position')
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

    public function getEpsg($srid) {
        $epsg = \DB::table('spatial_ref_sys')
            ->where('srid', $srid)
            ->first();
        return response()->json($epsg);
    }

    // POST

    public function addGeometry(Request $request) {
        $user = auth()->user();
        if(!$user->can('create_edit_geodata')) {
            return response()->json([
                'error' => __('You do not have the permission to add geometric data')
            ], 403);
        }
        $this->validate($request, [
            'collection' => 'required|json',
            'srid' => 'required|integer',
            'metadata' => 'nullable|json',
        ]);

        $objs = Geodata::createFromFeatureCollection(json_decode($request->get('collection')), $request->get('srid'), json_decode($request->get('metadata')), $user);
        return response()->json($objs);
    }

    public function addLayer(Request $request) {
        $user = auth()->user();
        if(!$user->can('create_edit_geodata')) {
            return response()->json([
                'error' => __('You do not have the permission to add layers')
            ], 403);
        }
        $this->validate($request, [
            'name' => 'required|string',
            'url' => 'required|string',
            'type' => 'required|string',
            'overlay' => 'nullable|boolean_string',
        ]);

        $name = $request->get('name');
        $url = $request->get('url');
        $type = $request->get('type');
        $isOverlay = $request->has('overlay') && $request->get('overlay') == 'true';

        $layer = AvailableLayer::createFromArray([
            'name' => $name,
            'url' => $url,
            'type' => $type,
            'opacity' => 1,
            'visible' => true,
            'is_overlay' => $isOverlay,
        ]);

        return response()->json($layer);
    }

    // PATCH

    public function updateLayer($id, Request $request) {
        $user = auth()->user();
        if(!$user->can('create_edit_geodata')) {
            return response()->json([
                'error' => __('You do not have the permission to update layers')
            ], 403);
        }
        $this->validate($request, AvailableLayer::patchRules);
        try {
            $layer = AvailableLayer::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This layer does not exist')
            ], 400);
        }

        $requestData = $request->only(array_keys(AvailableLayer::patchRules));
        $layer->patch($requestData);
        return response()->json(null, 204);
    }

    // PUT

    // DELETE

    public function deleteLayer($id) {
        $user = auth()->user();
        if(!$user->can('upload_remove_geodata')) {
            return response()->json([
                'error' => __('You do not have the permission to delete layers')
            ], 403);
        }
        try {
            $layer = AvailableLayer::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This layer does not exist')
            ], 400);
        }
        if(isset($layer->entity_type_id) || $layer->type == 'unlinked') {
            return response()->json([
                'error' => __('This layer can not be deleted')
            ], 400);
        }

        $layer->delete();

        return response()->json(null, 204);
    }
}
