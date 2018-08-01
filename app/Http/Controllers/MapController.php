<?php

namespace App\Http\Controllers;

use App\AvailableLayer;
use App\Context;
use App\ContextType;
use App\Geodata;
use Phaza\LaravelPostgis\Geometries\Point;
use Phaza\LaravelPostgis\Geometries\LineString;
use Phaza\LaravelPostgis\Geometries\Polygon;
use Phaza\LaravelPostgis\Geometries\MultiPoint;
use Phaza\LaravelPostgis\Geometries\MultiLineString;
use Phaza\LaravelPostgis\Geometries\MultiPolygon;
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
                'error' => 'You do not have the permission to view the geo data'
            ], 403);
        }
        // layers: id => layer
        $layers = AvailableLayer::all()->getDictionary();
        // contexts: id => context
        $contexts = Context::all()->getDictionary();
        // geoObjects: id => geoO
        $geodata = Geodata::with(['context'])->get()->getDictionary();

        return response()->json([
            'layers' => $layers,
            'contexts' => $contexts,
            'geodata' => $geodata
        ]);
    }

    public function getLayers(Request $request) {
        $user = auth()->user();
        if(!$user->can('view_geodata')) {
            return response()->json([
                'error' => 'You do not have the permission to add geometric data'
            ], 403);
        }
        $basic = $request->query('basic');
        $dict = $request->query('d');
        $basicOnly = isset($basic);
        $asDict = isset($dict);
        $baselayers = AvailableLayer::where('is_overlay', false)
            ->orderBy('id')
            ->get();
        $overlayQuery = AvailableLayer::with('context_type')
            ->where('is_overlay', true)
            ->orderBy('id');
        if($basicOnly) {
            $overlayQuery->whereNull('context_type_id')
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
                'error' => 'You do not have the permission to add geometric data'
            ], 403);
        }
        $entityLayers = AvailableLayer::with(['context_type'])
            ->whereNotNull('context_type_id')
            ->orWhere('type', 'unlinked')
            ->orderBy('id')
            ->get();

        return response()->json($entityLayers);
    }

    public function getLayer($id) {
        $user = auth()->user();
        if(!$user->can('view_geodata')) {
            return response()->json([
                'error' => 'You do not have the permission to add geometric data'
            ], 403);
        }
        try {
            $layer = AvailableLayer::with('context_type')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This layer does not exist'
            ], 400);
        }

        return response()->json($layer);
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
                'error' => 'You do not have the permission to add geometric data'
            ], 403);
        }
        $this->validate($request, [
            'collection' => 'required|json',
            'srid' => 'required|integer'
        ]);

        $objs = Geodata::createFromFeatureCollection(json_decode($request->get('collection')), $request->get('srid'), $user);
        return response()->json($objs);
    }

    public function getEpsgByText(Request $request) {
        $srtext = $request->get('srtext');
        $epsg = \DB::table('spatial_ref_sys')
            ->where('srtext', $srtext)
            ->first();
        return response()->json($epsg);
    }

    public function addLayer(Request $request) {
        $user = auth()->user();
        if(!$user->can('create_edit_geodata')) {
            return response()->json([
                'error' => 'You do not have the permission to add layers'
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

        return response()->json($layer);
    }

    public function link(Request $request, $gid, $eid) {
        $user = auth()->user();
        if(!$user->can('link_geodata')) {
            return response()->json([
                'error' => 'You do not have the permission to link geo data'
            ], 403);
        }
        try {
            $geodata = Geodata::findOrFail($gid);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This geodata does not exist'
            ], 400);
        }
        try {
            $entity = Context::findOrFail($eid);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This entity does not exist'
            ], 400);
        }

        if(isset($entity->geodata_id)) {
            return response()->json([
                'error' => 'This entity is already linked to a geo object'
            ], 400);
        }

        try {
            $layer = AvailableLayer::where('context_type_id', $entity->context_type_id)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Entity layer not found'
            ], 400);
        }

        if($layer->type != 'all') {
            $typeMatched = false;
            $upperType = strtoupper($layer->type);
            if(($geodata->geom instanceof Polygon || $geodata->geom instanceof MultiPolygon) && ends_with($upperType, 'POLYGON')) {
                $typeMatched = true;
            } else if(($geodata->geom instanceof LineString || $geodata->geom instanceof MultiLineString) && ends_with($upperType, 'LINESTRING')) {
                $typeMatched = true;
            } else if(($geodata->geom instanceof Point || $geodata->geom instanceof MultiPoint) && ends_with($upperType, 'POINT')) {
                $typeMatched = true;
            }
            if(!$typeMatched) {
                $geoType = get_class($geodata->geom);
                return response()->json([
                    'error' => "Layer type ('$layer->type') does not match type of geo object ('$geoType')"
                ], 400);
            }
        }

        $entity->geodata_id = $gid;
        $entity->save();

        return response()->json(null, 204);
    }

    // PUT

    // PATCH

    public function updateGeometry($id, Request $request) {
        $user = auth()->user();
        if(!$user->can('create_edit_geodata')) {
            return response()->json([
                'error' => 'You do not have the permission to edit geometric data'
            ], 403);
        }
        $this->validate($request, [
            'feature' => 'required|json',
            'srid' => 'required|integer'
        ]);

        try {
            $geodata = Geodata::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This geodata does not exist'
            ], 400);
        }
        $geodata->updateGeometry(json_decode($request->get('feature')), $request->get('srid'), $user);
    }

    public function updateLayer($id, Request $request) {
        $user = auth()->user();
        if(!$user->can('create_edit_geodata')) {
            return response()->json([
                'error' => 'You do not have the permission to update layers'
            ], 403);
        }
        $this->validate($request, AvailableLayer::patchRules);
        try {
            $layer = AvailableLayer::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This layer does not exist'
            ], 400);
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
        $user = auth()->user();
        if(!$user->can('upload_remove_geodata')) {
            return response()->json([
                'error' => 'You do not have the permission to delete geo data'
            ], 403);
        }
        try {
            $geodata = Geodata::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This geodata does not exist'
            ], 400);
        }
        $geodata->delete();

        return response()->json(null, 204);
    }

    public function unlink(Request $request, $gid, $eid) {
        $user = auth()->user();
        if(!$user->can('link_geodata')) {
            return response()->json([
                'error' => 'You do not have the permission to unlink geo data'
            ], 403);
        }
        try {
            Geodata::findOrFail($gid);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This geodata does not exist'
            ], 400);
        }
        try {
            $entity = Context::findOrFail($eid);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This entity does not exist'
            ], 400);
        }

        if($entity->geodata_id != $gid) {
            return response()->json([
                'error' => 'The entity is not linked to the provided geo object'
            ], 400);
        }

        $entity->geodata_id = NULL;
        $entity->save();

        return response()->json(null, 204);
    }
}
