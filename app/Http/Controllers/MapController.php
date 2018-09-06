<?php

namespace App\Http\Controllers;

use App\AvailableLayer;
use App\Entity;
use App\EntityType;
use App\Geodata;
use App\Helpers;
use Phaza\LaravelPostgis\Geometries\Point;
use Phaza\LaravelPostgis\Geometries\LineString;
use Phaza\LaravelPostgis\Geometries\Polygon;
use Phaza\LaravelPostgis\Geometries\MultiPoint;
use Phaza\LaravelPostgis\Geometries\MultiLineString;
use Phaza\LaravelPostgis\Geometries\MultiPolygon;
use \wapmorgan\UnifiedArchive\UnifiedArchive;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

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
        // entities: id => entity
        $entities = Entity::all()->getDictionary();
        // geoObjects: id => geoO
        $geodata = Geodata::with(['entity'])->get()->getDictionary();

        return response()->json([
            'layers' => $layers,
            'entities' => $entities,
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
                'error' => 'You do not have the permission to add geometric data'
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
                'error' => 'You do not have the permission to add geometric data'
            ], 403);
        }
        try {
            $layer = AvailableLayer::with('entity_type')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This layer does not exist'
            ], 400);
        }

        return response()->json($layer);
    }

    public function getGeometriesByLayer($id) {
        $user = auth()->user();
        if(!$user->can('view_geodata')) {
            return response()->json([
                'error' => 'You do not have the permission to get layers'
            ], 403);
        }

        try {
            $layer = AvailableLayer::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This layer does not exist'
            ]);
        }
        $query = Geodata::with(['entity']);
        if($layer->type == 'unlinked') {
            $query->doesntHave('entity');
        } else if(isset($layer->entity_type_id)) {
            $query->whereHas('entity', function($q) use ($layer) {
                $q->where('entity_type_id', $layer->entity_type_id);
            });
        }
        $geodata = $query->get();

        return response()->json($geodata);
    }

    public function getEpsg($srid) {
        $epsg = \DB::table('spatial_ref_sys')
            ->where('srid', $srid)
            ->first();
        return response()->json($epsg);
    }

    public function exportLayer(Request $request, $id) {
        $user = auth()->user();
        if(!$user->can('view_geodata')) {
            return response()->json([
                'error' => 'You do not have the permission to export layers'
            ], 403);
        }

        try {
            $layer = AvailableLayer::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This layer does not exist'
            ]);
        }
        if(strtoupper($layer->type) != 'UNLINKED' && !isset($layer->entity_type_id)) {
            return response()->json([
                'error' => 'This layer does not support export'
            ]);
        }
        if(strtoupper($layer->type) == 'UNLINKED') {
            $geodataBuilder = Geodata::doesntHave('entity');
        } else {
            $geodataBuilder = Geodata::with(['entity'])
                ->whereHas('entity', function($query) use($layer) {
                    $query->where('entity_type_id', $layer->entity_type_id);
                });
        }
        $query = Helpers::parseSql($geodataBuilder);
        $type = strtoupper($request->query('type', 'geojson'));
        $srid = strtoupper($request->query('srid', '4326'));
        $contentType = 'text/plain';
        switch($type) {
            case 'CSV':
                $geometry = 'AS_XYZ';
                $separator = 'COMMA';
                $exptype = 'CSV';
                $suffix = '.csv';
                $contentType = 'text/csv';
                break;
            case 'WKT':
                $geometry = 'AS_WKT';
                $separator = 'COMMA';
                $exptype = 'CSV';
                $suffix = '.csv';
                $contentType = 'text/csv';
                break;
            case 'KML':
            case 'KMZ':
                $exptype = 'KML';
                $suffix = '.kml';
                $contentType = 'application/vnd.google-earth.kml+xml';
                break;
            case 'GML':
                $exptype = 'GML';
                $suffix = '.gml';
                $contentType = 'application/gml+xml';
                break;
            case 'GEOJSON':
                //GeoJSON is default
            default:
                $exptype = 'GeoJSON';
                $suffix = '.json';
                $contentType = 'application/geo+json';
                break;
        }
        $host = env('DB_HOST');
        $port = env('DB_PORT');
        $user = env('DB_USERNAME');
        $db = env('DB_DATABASE');
        $pw = env('DB_PASSWORD');
        $dt = date('dmYHis');
        $tmpFile = '/tmp/export-'.$dt.$suffix;
        $command = "ogr2ogr -f \"$exptype\" $tmpFile PG:\"host=$host port=$port user=$user dbname=$db password=$pw\" -sql \"$query\"";
        if($srid != '4326') {
            $command .= " -t_srs EPSG:$srid";
        }
        if($exptype == 'CSV') {
            $command .= " -lco GEOMETRY=$geometry -lco SEPARATOR=$separator -lco LINEFORMAT=LF";
        }
        $process = new Process($command);
        $process->run();
        if(!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        if($type == 'KMZ') {
            $contentType = 'application/vnd.google-earth.kmz';
            $tmpZip = '/tmp/export-kml-'.$dt.'.zip';
            UnifiedArchive::archiveNodes($tmpFile, $tmpZip);
            // overwrite tmpFile path, because the zip should be downloaded
            unlink($tmpFile);
            $tmpFile = $tmpZip;
        }
        // get raw parsed content
        $content = file_get_contents($tmpFile);
        // delete tmp file
        unlink($tmpFile);
        return response(base64_encode($content))->header('Content-Type', $contentType);
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
            $entity = Entity::findOrFail($eid);
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
            $layer = AvailableLayer::where('entity_type_id', $entity->entity_type_id)->firstOrFail();
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

    public function deleteLayer($id) {
        $user = auth()->user();
        if(!$user->can('upload_remove_geodata')) {
            return response()->json([
                'error' => 'You do not have the permission to delete layers'
            ], 403);
        }
        try {
            $layer = AvailableLayer::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This layer does not exist'
            ], 400);
        }
        if(isset($layer->entity_type_id) || $layer->type == 'unlinked') {
            return response()->json([
                'error' => 'This layer can not be deleted'
            ], 400);
        }

        $layer->delete();

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
            $entity = Entity::findOrFail($eid);
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
