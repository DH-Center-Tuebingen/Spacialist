<?php

namespace App\Http\Controllers;
use App\AvailableLayer;
use App\Geodata;
use App\Context;
use App\Helpers;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use \Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use \wapmorgan\UnifiedArchive\UnifiedArchive;

class OverlayController extends Controller {
    public $availableGeometryTypes = Geodata::availableGeometryTypes;

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

    public function getContextOverlays() {
        $user = \Auth::user();
        if(!$user->can('view_geodata')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $layers = AvailableLayer::with(['context_type'])
            ->whereNotNull('context_type_id')
            ->orWhere('type', 'unlinked')
            ->get();
        foreach($layers as &$layer) {
            if(isset($layer->context_type)) {
                $layer->thesaurus_url = $layer->context_type->thesaurus_url;
            } else {
                unset($layer->thesaurus_url);
            }
            unset($layer->context_type);
        }
        return response()->json([
            'layers' => $layers
        ]);
    }

    public function getGeometryTypes() {
        return response()->json($this->availableGeometryTypes);
    }

    public function exportLayer($id, $type = 'geojson') {
        $user = \Auth::user();
        if(!$user->can('view_geodata')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }

        try {
            $layer = AvailableLayer::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This layer does not exist'
            ]);
        }
        if(strtoupper($layer->type) != 'UNLINKED' && !isset($layer->context_type_id)) {
            return response()->json([
                'error' => 'This layer does not support export'
            ]);
        }
        if(strtoupper($layer->type) == 'UNLINKED') {
            $linkedGeodataIds = Context::whereNotNull('geodata_id')->pluck('geodata_id');
            $geodataBuilder = Geodata::whereNotIn('id', $linkedGeodataIds);
        } else {
            $geodataBuilder = Geodata::join('contexts', 'contexts.geodata_id', '=', 'geodata.id')
                ->where('contexts.context_type_id', $layer->context_type_id)
                ->select('geodata.*');
        }
        $query = Helpers::parseSql($geodataBuilder);

        $type = strtoupper($type);
        switch($type) {
            case 'CSV':
                $geometry = 'AS_XYZ';
                $separator = 'COMMA';
                $exptype = 'CSV';
                $suffix = '.csv';
                break;
            case 'WKT':
                $geometry = 'AS_WKT';
                $separator = 'COMMA';
                $exptype = 'CSV';
                $suffix = '.csv';
                break;
            case 'KML':
            case 'KMZ':
                $exptype = 'KML';
                $suffix = '.kml';
                break;
            case 'GML':
                $exptype = 'GML';
                $suffix = '.gml';
                break;
            case 'GEOJSON':
                //GeoJSON is default
            default:
                $exptype = 'GeoJSON';
                $suffix = '.json';
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
        if($exptype == 'CSV') {
            $command .= " -lco GEOMETRY=$geometry -lco SEPARATOR=$separator -lco LINEFORMAT=LF";
        }
        $process = new Process($command);
        $process->run();

        if(!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        if($type == 'KMZ') {
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

        return response(base64_encode($content));
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
