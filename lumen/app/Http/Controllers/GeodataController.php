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
use App\Helpers;
use Phaza\LaravelPostgis\Geometries\Geometry;
use Phaza\LaravelPostgis\Geometries\Point;
use Phaza\LaravelPostgis\Geometries\LineString;
use Phaza\LaravelPostgis\Geometries\Polygon;
use Phaza\LaravelPostgis\Exceptions\UnknownWKTTypeException;
use Zizaco\Entrust;
use \DB;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GeodataController extends Controller {
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    // GET

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

    public function getEpsgCodes() {
        return response()->json(DB::table('spatial_ref_sys')
            ->select('auth_name', 'auth_srid', 'srtext')
            ->get()
        );
    }

    public function wktToGeojson($wkt) {
        if(!isset($wkt)) return; // null or empty
        // the GET request adds % for spaces
        $wkt = str_replace('%20', ' ', $wkt);
        $parsed = Helpers::parseWkt($wkt);
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

    // POST

    public function add(Request $request) {
        $user = \Auth::user();
        if(!$user->can('create_edit_geodata')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }

        $this->validate($request, [
            'coords' => 'required|json',
            'type' => 'required|geom_type'
        ]);

        $coords = json_decode($request->get('coords'));
        $type = $request->get('type');
        $geodata = new Geodata();

        $this->parseTypeCoords($type, $coords, $geodata);

        $geodata->lasteditor = $user['name'];
        $geodata->save();
        return response()->json([
            'geodata' => [
                'geodata' => $geodata->geom->jsonSerialize(),
                'id' => $geodata->id
            ]
        ]);
    }

    // PATCH

    // PUT

    public function put(Request $request, $id){
        $user = \Auth::user();
        if(!$user->can('create_edit_geodata')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }

        $this->validate($request, [
            'coords' => 'json',
            'type' => 'geom_type',
            'color' => 'color'
        ]);


        try {
            $geodata = Geodata::find($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This geodata does not exist'
            ]);
        }

        if($request->has(['coords', 'type'])){
            $coords = json_decode($request->get('coords'));
            $type = $request->get('type');
            $this->parseTypeCoords($type, $coords, $geodata);
        }

        if($request->has('color')){
            $geodata->color = $request->get('color');
        }

        $geodata->lasteditor = $user['name'];
        $geodata->save();
        return response()->json([
            'geodata' => [
                'geodata' => $geodata->geom->jsonSerialize(),
                'id' => $geodata->id,
                'color' => $geodata->color
            ]
        ]);
    }

    // DELETE

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
    }

    // OTHER FUNCTIONS

    private function parseTypeCoords($type, $coords, $geodata) {
        switch($type) {
            case 'Point':
                $coords = $coords[0];
                $geodata->geom = new Point($coords->lat, $coords->lng);
                break;
            case 'LineString':
                $lines = [];
                foreach($coords as $coord) {
                    $lines[] = new Point($coord->lat, $coord->lng);
                }
                $geodata->geom = new LineString($lines);
                break;
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
}
