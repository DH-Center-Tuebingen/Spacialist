<?php

namespace App\Http\Controllers;
use Log;
use App\User;
use App\Permission;
use App\Role;
use App\Geodata;
use App\Context;
use App\ContextType;
use App\Attribute;
use App\AttributeValue;
use App\ThConcept;
use App\ContextAttribute;
use Phaza\LaravelPostgis\Geometries\Geometry;
use Phaza\LaravelPostgis\Geometries\Point;
use Phaza\LaravelPostgis\Geometries\LineString;
use Phaza\LaravelPostgis\Geometries\Polygon;
use Phaza\LaravelPostgis\Exceptions\UnknownWKTTypeException;
use Zizaco\Entrust;
use \DB;
use Illuminate\Http\Request;

class GeodataController extends Controller {
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    private function parseWkt($wkt) {
        try {
            $geom = Geometry::getWKTClass($wkt);
            $parsed = $geom::fromWKT($wkt);
            return $parsed;
        } catch(UnknownWKTTypeException $e) {
            return -1;
        }
    }

    public function wktToGeojson($wkt) {
        if($wkt == null) return; // null or empty
        $parsed = $this->parseWkt($wkt);
        if($parsed !== -1) {
            return response()->json([
                'geometry' => $parsed
            ]);
        } else {
            return response()->json([
                'error' => 'unsupported_wkt'
            ]);
        }
    }

    public function delete($id) {
        $user = \Auth::user();
        if(!$user->can('upload_remove_geodata')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $linkedContexts = Context::where('geodata_id', '=', $id)->get();
        foreach($linkedContexts as $context) {
            $context->geodata_id = null;
            $context->save();
        }
        Geodata::find($id)->delete();
        return response()->json([
            'success' => ''
        ]);
    }

    private function parseTypeCoords($type, $coords, $geodata) {
        switch($type) {
            case 'marker':
            case 'Point':
                $coords = $coords[0];
                $geodata->geom = new Point($coords->lat, $coords->lng);
                break;
            case 'polyline':
            case 'LineString':
                $lines = [];
                foreach($coords as $coord) {
                    $lines[] = new Point($coord->lat, $coord->lng);
                }
                $geodata->geom = new LineString($lines);
                break;
            case 'polygon':
            case 'Polygon':
                $lines = [];
                foreach($coords[0] as $coord) {
                    $lines[] = new Point($coord->lat, $coord->lng);
                }
                $linestring = new LineString($lines);
                $geodata->geom = new Polygon([ $linestring ]);
                break;
        }
    }

    public function put(Request $request, $id){
        $user = \Auth::user();
        if(!$user->can('create_edit_geodata')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $coords = json_decode($request->get('coords'));
        $type = $request->get('type');
        $geodata = Geodata::find($id);

        parseTypeCoords($type, $coords, $geodata);

        $geodata->lasteditor = $user['name'];
        $geodata->save();
        return response()->json([
            'geodata' => [
                'geodata' => $geodata->geom->jsonSerialize(),
                'id' => $geodata->id
            ]
        ]);
    }

    public function add(Request $request) {
        $user = \Auth::user();
        if(!$user->can('create_edit_geodata')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $coords = json_decode($request->get('coords'));
        $type = $request->get('type');
        $geodata = new Geodata();

        parseTypeCoords($type, $coords, $geodata);

        $geodata->lasteditor = $user['name'];
        $geodata->save();
        return response()->json([
            'geodata' => [
                'geodata' => $geodata->geom->jsonSerialize(),
                'id' => $geodata->id
            ]
        ]);
    }



    public function get() {
        $user = \Auth::user();
        if(!$user->can('view_geodata')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $geoms = Geodata::all();
        $geodataList = [];
        foreach($geoms as $geom) {
            $geodataList[] = [
                'geodata' => $geom->geom->jsonSerialize(),
                'id' => $geom->id,
                'color' => $geom->color,
            ];
        }
        return response()->json([
            'geodata' => $geodataList
        ]);
    }
}
