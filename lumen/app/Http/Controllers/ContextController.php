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
use App\AvailableLayer;
use App\Helpers;
use Phaza\LaravelPostgis\Geometries\Geometry;
use Phaza\LaravelPostgis\Geometries\Point;
use Phaza\LaravelPostgis\Geometries\LineString;
use Phaza\LaravelPostgis\Geometries\Polygon;
use Phaza\LaravelPostgis\Geometries\MultiPoint;
use Phaza\LaravelPostgis\Geometries\MultiLineString;
use Phaza\LaravelPostgis\Geometries\MultiPolygon;
use Phaza\LaravelPostgis\Exceptions\UnknownWKTTypeException;
use Zizaco\Entrust;
use \DB;
use Illuminate\Http\Request;

class ContextController extends Controller {
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    public function getContextData($id) {
        $user = \Auth::user();
        if(!$user->can('view_concept_props')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        return response()->json([
            'data' => $this->getData($id)
        ]);
    }

    private function getData($id) {
        $data = DB::table('attribute_values as av')->select('av.*', 'a.datatype', 'a.thesaurus_root_url')->join('attributes as a', 'av.attribute_id', '=', 'a.id')->where('context_id', $id)->get();
        foreach($data as &$attr) {
            if($attr->datatype == 'literature') {
                $attr->literature_info = DB::table('literature')->where('id', $attr->str_val)->first();
            } else if($attr->datatype == 'string-sc' || $attr->datatype == 'string-mc') {
                $attr->val = DB::table('th_concept')
                    ->select('id as narrower_id',
                        DB::raw("'".DB::table('getconceptlabelsfromurl')
                        ->where('concept_url', $attr->thesaurus_val)
                        ->where('short_name', 'de')
                        ->value('label')."' as narr")
                    )
                    ->where('concept_url', '=', $attr->thesaurus_val)
                    ->first();
            } else if($attr->datatype == 'dimension') {
                $jsonVal = json_decode($attr->json_val);
                if(!isset($jsonVal)) continue;
                else $attrVal = array();

                if(isset($jsonVal->B)){
                    $attrVal['B'] = $jsonVal->B;
                }
                if(isset($jsonVal->H)){
                    $attrVal['H'] = $jsonVal->H;
                }
                if(isset($jsonVal->T)){
                    $attrVal['T'] = $jsonVal->T;
                }
                if(isset($jsonVal->unit)){
                    $attrVal['unit'] = $jsonVal->unit;
                }
                $attr->val = json_encode($attrVal);
            } else if($attr->datatype == 'epoch') {
                $jsonVal = json_decode($attr->json_val);
                if(!isset($jsonVal)) continue;

                if(isset($jsonVal->startLabel)){
                    $attrVal['startLabel'] = $jsonVal->startLabel;
                }
                if(isset($jsonVal->start)){
                    $attrVal['start'] = $jsonVal->start;
                }
                if(isset($jsonVal->endLabel)){
                    $attrVal['endLabel'] = $jsonVal->endLabel;
                }
                if(isset($jsonVal->end)){
                    $attrVal['end'] = $jsonVal->end;
                }
                if(isset($jsonVal->epoch)){
                    \Log::info('$jsonVal->epoch');
                    \Log::info(print_r($jsonVal->epoch, true));
                    $attrVal['epoch'] = DB::table('th_concept')
                                        ->select('id as narrower_id',
                                            DB::raw("'".DB::table('getconceptlabelsfromid')
                                            ->where('concept_id', $jsonVal->epoch->narrower_id)
                                            ->where('short_name', 'de')
                                            ->value('label')."' as narr")
                                        )
                                        ->where('id', '=', $jsonVal->epoch->narrower_id)
                                        ->first();
                }
                $attr->val = json_encode($attrVal);
            } else if($attr->datatype == 'geography') {
                $tmp = AttributeValue::find($attr->id);
                $attr->val = $tmp->geography_val->toWKT();
            } else if($attr->datatype == 'context') {
                $ctx = Context::find($attr->context_val);
                $attr->val = [
                    'name' => $ctx->name,
                    'id' => $ctx->id
                ];
            }
        }
        return $data;
    }

    public static function getLabel($thesaurus_url, $lang = 'de') {
        $label = DB::table('th_concept_label as lbl')
            ->join('th_language as lang', 'lang.id', '=', 'lbl.language_id')
            ->join('th_concept as con', 'lbl.concept_id', '=', 'con.id')
            ->where('con.concept_url', '=', $thesaurus_url)
            ->orderBy('lbl.concept_label_type', 'asc')
            ->orderByRaw("lang.short_name = '$lang' ASC")
            ->value('lbl.label');
        return $label;
    }

    public function search(Request $request) {
        if(!$request->has('val')) return response()->json();
        $val = $request->get('val');
        if($request->has('lang')) $lang = $request->get('lang');
        else $lang = 'de';

        $matchedConcepts = DB::table('th_concept_label as l')
            ->select('c.concept_url', 'c.id', 'l.label')
            ->join('th_concept as c', 'c.id', '=', 'l.concept_id')
            ->join('th_language as lng', 'l.language_id', '=', 'lng.id')
            ->where([
                ['label', 'ilike', '%' . $val . '%'],
                ['lng.short_name', '=', $lang]
            ])
            ->groupBy('c.id', 'l.label')
            ->orderBy('l.label')
            ->get();
        return response()->json($matchedConcepts);
    }

    public function searchContext(Request $request) {
        if(!$request->has('val')) return response()->json();
        $val = $request->get('val');

        $matchingContexts = Context::where('name', 'ilike', '%'.$val.'%')
            ->select('name', 'id')
            ->orderBy('name')
            ->get();
        return response()->json($matchingContexts);
    }

    public function deleteContextType($id) {
        ContextType::find($id)->delete();
    }

    public function addContextType(Request $request) {
        if(!$request->has('concept_url') || !$request->has('type')) {
            return response()->json([
                'error' => 'Missing parameter'
            ]);
        }
        $curl = $request->get('concept_url');
        $type = $request->get('type');
        $geomtype = $request->get('geomtype');
        $cType = new ContextType();
        $cType->thesaurus_url = $curl;
        $cType->type = $type;
        $cType->save();
        $cType->label = $this->getLabel($curl);

        $layer = new AvailableLayer();
        $layer->name = '';
        $layer->url = '';
        $layer->type = $geomtype;
        $layer->opacity = 1;
        $layer->visible = true;
        $layer->is_overlay = true;
        $layer->position = AvailableLayer::where('is_overlay', '=', true)->max('position') + 1;
        $layer->context_type_id = $cType->id;
        $layer->color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
        $layer->save();

        return response()->json([
            'contexttype' => $cType,
            'layer' => $layer
        ]);
    }

    public function editContextType(Request $request) {
        $id = $request->get('ctid');
        $newUrl = $request->get('new_url');
        $ct = ContextType::find($id);
        $ct->thesaurus_url = $newUrl;
        $ct->save();
    }

    public function addAttribute(Request $request) {
        if(!$request->has('label_id') || !$request->has('datatype')) {
            return response()->json([
                'error' => 'Missing parameter.'
            ]);
        }
        $lid = $request->get('label_id');
        $datatype = $request->get('datatype');
        $curl = ThConcept::find($lid)->concept_url;
        $attr = new Attribute();
        $attr->thesaurus_url = $curl;
        $attr->datatype = $datatype;
        if($request->has('parent_id')) {
            $pid = $request->get('parent_id');
            $purl = ThConcept::find($pid)->concept_url;
            $attr->thesaurus_root_url = $purl;
        }
        $attr->save();
        $attr->label = $this->getLabel($curl);
        if(isset($purl)) $attr->root_label = $this->getLabel($purl);

        return response()->json([
            'attribute' => $attr
        ]);
    }

    public function addAttributeToContextType(Request $request) {
        if(!$request->has('aid') || !$request->has('ctid')) {
            return response()->json([
                'error' => 'Missing parameter. Either aid or ctid is missing.'
            ]);
        }
        $aid = $request->get('aid');
        $ctid = $request->get('ctid');

        $attrsCnt = ContextAttribute::where('context_type_id', '=', $ctid)->count();
        $ca = new ContextAttribute();
        $ca->context_type_id = $ctid;
        $ca->attribute_id = $aid;
        $ca->position = $attrsCnt + 1; // add new attribute to the end
        $ca->save();

        $a = Attribute::find($aid);
        $ca->val = $this->getLabel($a->thesaurus_url);
        $ca->datatype = $a->datatype;

        return response()->json([
            'attribute' => $ca
        ]);
    }

    public function removeAttributeFromContextType(Request $request) {
        if(!$request->has('aid') || !$request->has('ctid')) {
            return response()->json([
                'error' => 'Missing parameter. Either aid or ctid is missing.'
            ]);
        }
        $aid = $request->get('aid');
        $ctid = $request->get('ctid');

        $ca = ContextAttribute::where([
            ['attribute_id', '=', $aid],
            ['context_type_id', '=', $ctid]
        ])->first();
        $pos = $ca->position;
        $ca->delete();

        $successors = ContextAttribute::where([
                ['position', '>', $pos],
                ['context_type_id', '=', $ctid]
            ])->get();
        foreach($successors as $s) {
            $s->position--;
            $s->save();
        }
    }

    public function deleteAttribute($id) {
        Attribute::find($id)->delete();
    }

    public function moveAttributeUp(Request $request) {
        if(!$request->has('aid') || !$request->has('ctid')) {
            return response()->json([
                'error' => 'Missing parameter. Either aid or ctid is missing.'
            ]);
        }
        $aid = $request->get('aid');
        $ctid = $request->get('ctid');

        $ca = ContextAttribute::where([
            ['attribute_id', '=', $aid],
            ['context_type_id', '=', $ctid]
        ])->first();

        if($ca->position == 1) {
            return response()->json([
                'error' => 'Element is already topmost element'
            ]);
        }
        $ca2 = ContextAttribute::where([
            ['position', '=', $ca->position-1],
            ['context_type_id', '=', $ctid]
        ])->first();

        $ca->position--;
        $ca2->position++;
        $ca->save();
        $ca2->save();
        return response()->json();
    }

    public function moveAttributeDown(Request $request) {
        if(!$request->has('aid') || !$request->has('ctid')) {
            return response()->json([
                'error' => 'Missing parameter. Either aid or ctid is missing.'
            ]);
        }
        $aid = $request->get('aid');
        $ctid = $request->get('ctid');

        $ca = ContextAttribute::where([
            ['attribute_id', '=', $aid],
            ['context_type_id', '=', $ctid]
        ])->first();

        if($ca->position == ContextAttribute::where('context_type_id', '=', $ctid)->count()) {
            return response()->json([
                'error' => 'Element is already bottommost element'
            ]);
        }
        $ca2 = ContextAttribute::where([
            ['position', '=', $ca->position+1],
            ['context_type_id', '=', $ctid]
        ])->first();

        $ca->position++;
        $ca2->position--;
        $ca->save();
        $ca2->save();
        return response()->json();
    }

    public function getOccurrenceCount($id) {
        $cnt = Context::where('context_type_id', '=', $id)->count();
        return response()->json([
            'count' => $cnt
        ]);
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

    public function wktToGeojson(Request $request) {
        if(!$request->has('wkt')) return;
        $wkt = $request->get('wkt');
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

    public function get() {
        return response()->json(
            DB::table('context_types as c')
                ->select('c.thesaurus_url as index', 'c.id as context_type_id', 'a.id as aid', 'a.datatype', 'c.type', 'ca.position',
                    DB::raw("(select label from getconceptlabelsfromurl where concept_url = c.thesaurus_url and short_name = 'de' limit 1) as title"),
                    DB::raw("(select label from getconceptlabelsfromurl where concept_url = a.thesaurus_url and short_name = 'de' limit 1) as val")
                )
                ->leftJoin('context_attributes as ca', 'c.id', '=', 'ca.context_type_id')
                ->leftJoin('attributes as a', 'ca.attribute_id', '=', 'a.id')
                ->where('c.type', '=', '0')
                ->orderBy('title', 'asc')
                ->orderBy('ca.position', 'asc')
                ->get()
        );
    }

    public function getAvailableAttributeTypes() {
        return response()->json([
            'types' => [
                [
                    'datatype' => 'string',
                    'description' => 'attribute.string.desc'
                ],
                [
                    'datatype' => 'stringf',
                    'description' => 'attribute.stringf.desc'
                ],
                [
                    'datatype' => 'double',
                    'description' => 'attribute.double.desc'
                ],
                [
                    'datatype' => 'string-sc',
                    'description' => 'attribute.string-sc.desc'
                ],
                [
                    'datatype' => 'string-mc',
                    'description' => 'attribute.string-mc.desc'
                ],
                [
                    'datatype' => 'epoch',
                    'description' => 'attribute.epoch.desc'
                ],
                [
                    'datatype' => 'date',
                    'description' => 'attribute.date.desc'
                ],
                [
                    'datatype' => 'dimension',
                    'description' => 'attribute.dimension.desc'
                ],
                [
                    'datatype' => 'list',
                    'description' => 'attribute.list.desc'
                ],
                [
                    'datatype' => 'geography',
                    'description' => 'attribute.geography.desc'
                ],
                [
                    'datatype' => 'integer',
                    'description' => 'attribute.integer.desc'
                ],
                [
                    'datatype' => 'percentage',
                    'description' => 'attribute.percentage.desc'
                ],
                [
                    'datatype' => 'context',
                    'description' => 'attribute.context.desc'
                ]
            ]
        ]);
    }

    public function getAttributes() {
        return response()->json([
            'attributes' => Attribute::select('*', DB::raw("(select label from getconceptlabelsfromurl where concept_url = thesaurus_url and short_name = 'de' limit 1) as label"), DB::raw("(select label from getconceptlabelsfromurl where concept_url = thesaurus_root_url and short_name = 'de' limit 1) as root_label"))->orderBy('label', 'asc')->get()
        ]);
    }

    public function getRecursive() {
        $user = \Auth::user();
        if(!$user->can('view_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }


        $contextEntries = ContextType::join('contexts', 'contexts.context_type_id', '=', 'context_types.id')->select('contexts.*', 'type as typeid', 'thesaurus_url as typename', DB::raw("(select label from getconceptlabelsfromurl where concept_url = thesaurus_url and short_name = 'de' limit 1) as typelabel"))->orderBy('rank')->get();

        $roots = array();
        $contexts = array();
        $children = array();

        foreach($contextEntries as $key => $context) {
            $contexts[$context->id] = $context;
            if(!$user->can('view_geodata')) {
                if(isset($contexts[$context->id]->geodata_id)){
                    unset($contexts[$context->id]->geodata_id);
                }
            }
            if(!isset($context->root_context_id)) {
                array_push($roots, $context->id);
            }
            else {
                if(!array_key_exists($context->root_context_id, $children)) {
                    $children[$context->root_context_id] = array();
                }
                array_push($children[$context->root_context_id], $context->id);
            }
        }

        $response = [   'contexts' => $contexts,
                        'roots' => $roots,
                        'children' => $children];


        // $rootFields = DB::select("
        // WITH RECURSIVE
        // q AS (
	    //     SELECT  c.*, 0 as reclevel
	    //     FROM    contexts c
	    //     WHERE   root_context_id IS NULL
	    //     UNION ALL
	    //     SELECT  cc.*, reclevel+1
	    //     FROM    q
	    //     JOIN    contexts cc
	    //     ON      cc.root_context_id = q.id
        // )
        // SELECT  q.*, ct.type as typeid, ct.thesaurus_url AS typename, (select label from getconceptlabelsfromurl where concept_url = ct.thesaurus_url and short_name = 'de' limit 1) as typelabel
        // FROM    q
        // JOIN context_types AS ct
        // ON q.context_type_id = ct.id
        // ORDER BY reclevel DESC
        // ");
        // $children = [];
        // foreach($rootFields as $key => $field) {
        //     if(array_key_exists($field->id, $children)) $tmpChildren = $children[$field->id];
        //     else $tmpChildren = array();
        //     $rootFields[$key]->children = $tmpChildren;
        //     $children[$field->root_context_id][] = $field;
        //     if($field->reclevel != 0) unset($rootFields[$key]);
        //
        //     if(!$user->can('view_geodata')) {
        //         if(isset($rootFields[$key]->geodata_id)){
        //             unset($rootFields[$key]->geodata_id);
        //         }
        //     }
        // }

        return response()->json($response);
    }

    public function linkGeodata($cid, $gid) {
        $user = \Auth::user();
        if(!$user->can('link_geodata')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        try {
            $context = Context::findOrFail($cid);
        } catch(Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This context does not exist'
            ]);
        }
        if(isset($context->geodata_id)) {
            return response()->json([
                'error' => 'This context is already linked to a geodata'
            ]);
        }
        try {
            $geodata = Geodata::findOrFail($gid);
        } catch(Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This geodata does not exist'
            ]);
        }
        $layer = AvailableLayer::where('context_type_id', '=', $context->context_type_id)->first();

        $undefinedError = [
            'error' => 'Layer Type doesn\'t match Geodata Type'
        ];
        if(($geodata->geom instanceof Point || $geodata->geom instanceof MultiPoint) && !Helpers::endsWith($layer->type, 'Point')) {
            return response()->json($undefinedError);
        } else if(($geodata->geom instanceof LineString || $geodata->geom instanceof MultiLineString) && !Helpers::endsWith($layer->type, 'Linestring')) {
            return response()->json($undefinedError);
        } else if(($geodata->geom instanceof Polygon || $geodata->geom instanceof MultiPolygon) && !Helpers::endsWith($layer->type, 'Polygon')) {
            return response()->json($undefinedError);
        }
        $context->geodata_id = $gid;
        $context->save();
        return response()->json([
            'context' => $context
        ]);
    }

    public function unlinkGeodata($cid) {
        $user = \Auth::user();
        if(!$user->can('link_geodata')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        try {
            $context = Context::findOrFail($cid);
        } catch(Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This context does not exist'
            ]);
        }
        $context->geodata_id = null;
        $context->save();
        return response()->json([
            'context' => $context
        ]);
    }

    public function getContextParents($id) {
        $user = \Auth::user();
        if(!$user->can('view_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $path = DB::select("
        WITH RECURSIVE
        q AS (
	        SELECT  c.*, 0 as reclevel
	        FROM    contexts c
	        WHERE   id = $id
	        UNION ALL
	        SELECT  cc.*, reclevel+1
	        FROM    q
	        JOIN    contexts cc
	        ON      q.root_context_id = cc.id
        )
        SELECT  q.id
        FROM    q
        ORDER BY reclevel DESC
        ");
        return response()->json([
            'path' => $path
        ]);
    }

    public function getContextByGeodata($id) {
        $user = \Auth::user();
        if(!$user->can('view_geodata') || !$user->can('view_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        return response()->json([
            'context_id' => Context::where('geodata_id', '=', $id)->value('id')
        ]);
    }

    public function deleteGeodata($id) {
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

    public function addGeodata(Request $request) {
        $user = \Auth::user();
        if(!$user->can('create_edit_geodata')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        \Log::info($request->get('coords'));
        \Log::info(print_r(json_decode($request->get('coords')), true));
        $coords = json_decode($request->get('coords'));
        $type = $request->get('type');
        if($request->has('id')) {
            $id = $request->get('id');
            $geodata = Geodata::find($id);
        } else {
            $geodata = new Geodata();
        }
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
                $geodata->geom = new MultiLineString([new LineString($lines)]);
                break;
            case 'polygon':
            case 'Polygon':
                $lines = [];
                foreach($coords[0] as $coord) {
                    $lines[] = new Point($coord->lat, $coord->lng);
                }
                $linestring = new LineString($lines);
                $geodata->geom = new MultiPolygon([new Polygon([ $linestring ])]);
                break;
        }
        $geodata->lasteditor = $user['name'];
        $geodata->save();
        return response()->json([
            'geodata' => [
                'geodata' => $geodata->geom->jsonSerialize(),
                'id' => $geodata->id
            ]
        ]);
    }

    public function getGeodata() {
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

    public function getChoices() {
        $user = \Auth::user();
        if(!$user->can('view_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $rows = DB::table('context_types as c')
        ->select('c.id as context_type_id', 'a.id as aid', 'a.datatype', 'a.thesaurus_root_url as root', 'ca.position',
            DB::raw("(select label from getconceptlabelsfromurl where concept_url = C.thesaurus_url and short_name = 'de' limit 1) AS title"),
            DB::raw("(select label from getconceptlabelsfromurl where concept_url = A.thesaurus_url and short_name = 'de' limit 1) AS val")
        )
        ->leftJoin('context_attributes as ca', 'c.id', '=', 'ca.context_type_id')
        ->leftJoin('attributes as a', 'ca.attribute_id', '=', 'a.id')
        ->where('a.datatype', '=', 'string-sc')
        ->orWhere('a.datatype', '=', 'string-mc')
        ->orWhere('a.datatype', '=', 'epoch')
        ->orderBy('title', 'asc')
        ->orderBy('ca.position', 'asc')
        ->get();
        foreach($rows as &$row) {
            if(!isset($row->root)) continue;
            $rootId = DB::table('th_concept')
                ->select('id')
                ->where('concept_url', '=', $row->root)
                ->first();
            if(!isset($rootId)) continue;
            $rootId = $rootId->id;
            $row->choices = DB::select("
                WITH RECURSIVE
                top AS (
                    SELECT br.broader_id, br.narrower_id, (select label from getconceptlabelsfromid where concept_id = br.broader_id and short_name = 'de' limit 1) as broad,
                            (select label from getconceptlabelsfromid where concept_id = br.narrower_id and short_name = 'de' limit 1) as narr
                    FROM th_broaders br
                    WHERE broader_id = $rootId
                    UNION
                    SELECT br.broader_id, br.narrower_id, (select label from getconceptlabelsfromid where concept_id = br.broader_id and short_name = 'de' limit 1) as broad,
                            (select label from getconceptlabelsfromid where concept_id = br.narrower_id and short_name = 'de' limit 1) as narr
                    FROM top t, th_broaders br
                    WHERE t.narrower_id = br.broader_id
                )
                SELECT *
                FROM top
                ORDER BY narr
            ");
        }
        return response()->json($rows);
    }

    public function duplicate($id) {
        $user = \Auth::user();
        if(!$user->can('duplicate_edit_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $toDuplicate = Context::find($id);
        $newDuplicate = $toDuplicate->replicate([
            'geodata_id'
        ]);
        $dupCounter = 0;
        do {
            $dupCounter++;
            $sameName = DB::table('contexts')
                ->where('name', '=', $toDuplicate->name . " ($dupCounter)")
                ->first();
        } while($sameName != null);
        $newDuplicate->name .= " ($dupCounter)";
        $siblings;
        if($toDuplicate->root_context_id === null) {
            $siblings = Context::whereNull('root_context_id')
                ->where('rank', '>', $toDuplicate->rank)
                ->get();
        } else {
            $siblings = Context::where('root_context_id', '=', $toDuplicate->root_context_id)
                ->where('rank', '>', $toDuplicate->rank)
                ->get();
        }
        foreach($siblings as $s) {
            $s->rank++;
            $s->save();
        }
        $newDuplicate->rank = $toDuplicate->rank + 1;
        $newDuplicate->save();
        $toDuplicateValues = AttributeValue::where('context_id', $id)
            ->get();
        foreach($toDuplicateValues as $value) {
            $newValue = $value->replicate();
            $newValue->context_id = $newDuplicate->id;
            $newValue->save();
        }
        return response()->json(['obj' => $newDuplicate]);
    }

    public function getArtifacts() {
        return response()->json(
            DB::table('context_types as c')
                ->select('c.thesaurus_url as index', 'c.id as context_type_id', 'a.id as aid', 'a.datatype', 'c.type', 'ca.position',
                    DB::raw("(select label from getconceptlabelsfromurl where concept_url = C.thesaurus_url and short_name = 'de' limit 1) AS title"),
                    DB::raw("(select label from getconceptlabelsfromurl where concept_url = A.thesaurus_url and short_name = 'de' limit 1) AS val")
                )
                ->leftJoin('context_attributes as ca', 'c.id', '=', 'ca.context_type_id')
                ->leftJoin('attributes as a', 'ca.attribute_id', '=', 'a.id')
                ->where('c.type', '=', '1')
                ->orderBy('title', 'asc')
                ->orderBy('ca.position', 'asc')
                ->get()
        );
    }

    public function getChildren($id) {
        $intId = filter_var($id, FILTER_VALIDATE_INT);
        if($intId === false || $intId <= 0) return;
        $user = \Auth::user();
        if(!$user->can('view_concept_props')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $rows = DB::select(
            "WITH RECURSIVE
            q AS (
                SELECT  c.*
                FROM    contexts c
                WHERE   id = $id
                UNION ALL
                SELECT  cc.*
                FROM    q
                JOIN    contexts cc
                ON      cc.root_context_id = q.id
            )
            SELECT  q.*, ct.type, ct.thesaurus_url AS typename, (select label from getconceptlabelsfromurl where concept_url = ct.thesaurus_url and short_name = 'de' limit 1) as typelabel
            FROM    q
            JOIN context_types AS ct
            ON q.context_type_id = ct.id
            ORDER BY id ASC"
        );
        $roots = array();
        foreach($rows as $row) {
            if(empty($row)) continue;
            $row->data = DB::table('attribute_values as av')->select('av.*', 'a.datatype')->join('attributes as a', 'av.attribute_id', '=', 'a.id')->where('context_id', $row->id)->get();
            if(!empty($row->root_context_id)) $roots[$row->root_context_id][] = $row;
        }
        return response()->json($roots);
    }

    public function set(Request $request) {
        $user = \Auth::user();
        $id = $request->get('id');

        if((isset($id) && !$user->can('duplicate_edit_concepts')) || (!isset($id) && !$user->can('create_concepts'))) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }

        $isUpdate = isset($id) && $id > 0;
        if($isUpdate) {
            $context = Context::find($id);
        } else {
            $context = new Context();
            $rank;
            if($request->has('root_cid')) {
                $rank = Context::where('root_context_id', '=', $request->get('root_cid'))->max('rank') + 1;
            } else {
                $rank = Context::whereNull('root_context_id')->max('rank') + 1;
            }
            $context->rank = $rank;
        }
        if($request->has('name')) $context->name = $request->get('name');
        if($request->has('context_type_id')) $context->context_type_id = $request->get('context_type_id');
        if($request->has('root_cid')) $context->root_context_id = $request->get('root_cid');
        $context->lasteditor = $user['name'];
        $context->save();

        $id = $context->id;
        $message = $this->updateOrInsert($request->except(['id', 'name', 'context_type_id', 'root_cid']), $id, $isUpdate, $user);
        if(isset($message['error'])){
            return response()->json($message);
        }
        return response()->json(['context' => $context]);
    }

    public function setProperties(Request $request) {
        $user = \Auth::user();
        if(!$user->can('duplicate_edit_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $id = $request->get('id');
        $upd = [];

        $geodata = Geodata::find($id);
        if($request->has('color')) {
            $geodata->color = $request->get('color');
        }
        if($request->has('lat') && $request->has('lng') && $geodata->geom instanceof Point) {
            $geodata->geom = new Point($request->get('lat'), $request->get('lng'));
        }
        $geodata->save();
        $ret = [
            'color' => $geodata->color
        ];
        if($geodata->geom instanceof Point) {
            $ret['lat'] = $geodata->geom->getLat();
            $ret['lng'] = $geodata->geom->getLng();
        }
        return response()->json($ret);
    }

    public function move(Request $request) {
        $user = \Auth::user();
        if(!$user->can('duplicate_edit_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $id = $request->get('id');
        $rank = $request->get('rank');
        $hasParent = $request->has('parent_id');
        $context = Context::find($id);
        $oldRank = $context->rank;
        $context->rank = $rank;

        $oldContexts;
        if($context->root_context_id !== null) {
            $oldContexts = Context::where('root_context_id', '=', $context->root_context_id)
                ->where('rank', '>', $oldRank)
                ->get();
        } else {
            $oldContexts = Context::whereNull('root_context_id')
                ->where('rank', '>', $oldRank)
                ->get();
        }
        foreach($oldContexts as $oc) {
            $oc->rank--;
            $oc->save();
        }

        $contexts;
        if($hasParent) {
            $parent = $request->get('parent_id');
            $context->root_context_id = $parent;
            $contexts = Context::where('root_context_id', '=', $parent)
                ->where('rank', '>=', $rank)
                ->get();
        } else {
            $context->root_context_id = null;
            $contexts = Context::whereNull('root_context_id')
                ->where('rank', '>=', $rank)
                ->get();
        }
        foreach($contexts as $c) {
            $c->rank++;
            $c->save();
        }
        $context->save();

    }

    public function setPossibility(Request $request) {
        $user = \Auth::user();
        if(!$user->can('duplicate_edit_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $user = \Auth::user();
        if($user == null) $user = ['name' => 'postgres']; //TODO remove after user auth has been fixed!
        $cid = $request->get('cid');
        $aid = $request->get('aid');
        $possibility = $request->get('possibility');
        $description = $request->get('possibility_description');

        $where = array(
            ['context_id', '=', $cid],
            ['attribute_id', '=', $aid]
        );
        $isSet = DB::table('attribute_values')
            ->where($where)
            ->count();
        if($isSet == null) { //insert
            DB::table('attribute_values')
                ->insert([
                    'context_id' => $cid,
                    'attribute_id' => $aid,
                    'possibility' => $possibility,
                    'possibility_description' => $description,
                    'lasteditor' => $user['name']
                ]);
        } else { //update
            DB::table('attribute_values')
                ->where($where)
                ->update([
                    'possibility' => $possibility,
                    'possibility_description' => $description,
                    'lasteditor' => $user['name']
                ]);
        }
        return response()->json(DB::table('attribute_values')
            ->where($where)->get());
    }


    public function delete($id) {
        $user = \Auth::user();
        if(!$user->can('delete_move_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }

        $context = Context::find($id);
        $siblings;
        if($context->root_context_id === null) {
            $siblings = Context::whereNull('root_context_id')
                ->where('rank', '>', $context->rank)
                ->get();
        } else {
            $siblings = Context::where('root_context_id', '=', $context->root_context_id)
                ->where('rank', '>', $context->rank)
                ->get();
        }
        foreach($siblings as $s) {
            $s->rank--;
            $s->save();
        }
        $context->delete();

        return response()->json();
    }

    public function updateOrInsert($request, $cid, $isUpdate, $user) {
        foreach($request as $key => $value) {
            if($value == 'null' || $value === null) continue;
            $ids = explode("_", $key);
            $aid = $ids[0];
            if(isset($ids[1]) && $ids[1] == 'desc') continue;
            $datatype = Attribute::find($aid)->datatype;
            $jsonArr = json_decode($value);
            if($datatype === 'string-sc') $jsonArr = [$jsonArr]; //"convert" to array

            if($datatype === 'epoch') {
                $startExists = false;
                if(isset($jsonArr->start)) {
                    $startExists = true;
                    $start = $jsonArr->start;
                    if(isset($jsonArr->startLabel) && $jsonArr->startLabel === 'bc') {
                        $start = -$start;
                    }
                }
                $endExists = false;
                if(isset($jsonArr->end)) {
                    $endExists = true;
                    $end = $jsonArr->end;
                    if(isset($jsonArr->endLabel) && $jsonArr->endLabel === 'bc') {
                        $end = -$end;
                    }
                }
                if($endExists && $startExists && $end < $start){
                    return [
                        'error' => 'End date should be later than start date.'
                    ];
                }
            }

            if(is_array($jsonArr) && ($datatype == 'string-sc' || $datatype == 'string-mc')) { //only string-sc and string-mc should be arrays
                if($isUpdate) {
                    $dbEntries = array(
                        ['context_id', $cid],
                        ['attribute_id', $aid]
                    );
                    $rows = AttributeValue::where($dbEntries)->get();
                    foreach($rows as $row) {
                        $alreadySet = false;
                        foreach($jsonArr as $k => $v) {
                            if($datatype === 'list') {
                                $set = $v->name;
                                $val = $row->str_val;
                            } else {
                                try {
                                    $con = ThConcept::findOrFail($v->narrower_id);
                                    $set = $con->concept_url;
                                    $val = $row->thesaurus_val;
                                } catch(Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                                    continue;
                                }
                            }
                            if($val === $set) {
                                unset($jsonArr[$k]);
                                $alreadySet = true;
                                break;
                            }
                        }
                        if(!$alreadySet) {
                            $del = array(
                                ['context_id', $cid],
                                ['attribute_id', $aid]
                            );
                            if($datatype === 'list') $del[] = ['str_val', $row->str_val];
                            else $del[] = ['thesaurus_val', $row->thesaurus_val];
                            AttributeValue::where($del)->delete();
                        }
                    }
                }
                foreach($jsonArr as $v) {
                    $attr = new AttributeValue();
                    $attr->context_id = $cid;
                    $attr->attribute_id = $aid;
                    $attr->lasteditor = $user['name'];
                    if($datatype === 'list') {
                        $attr->str_val = $v->name;
                    } else {
                        $set = ThConcept::find($v->narrower_id)->concept_url;
                        $attr->thesaurus_val = $set;
                    }
                    $attr->save();
                }
            } else {
                if($isUpdate) {
                    $alreadySet = false;
                    $attr;
                    $currAttrs = DB::table('attribute_values')->where('context_id', $cid)->get();
                    foreach($currAttrs as $currKey => $currVal) {
                        if($aid == $currVal->attribute_id) {
                            $alreadySet = true;
                            $attr = $currVal;
                            unset($currAttrs[$currKey]);
                            break;
                        }
                    }
                    if($alreadySet) {
                        $attrValue = AttributeValue::where([
                            ['context_id', '=', $attr->context_id],
                            ['attribute_id', '=', $attr->attribute_id],
                            ['id', '=', $attr->id]
                        ])->first();
                        if($value == '' || $value === null) {
                            AttributeValue::find($attrValue->id)->delete();
                            continue;
                        }
                    } else {
                        $attrValue = new AttributeValue();
                        $attrValue->context_id = $cid;
                        $attrValue->attribute_id = $aid;
                    }
                } else {
                    $attrValue = new AttributeValue();
                    $attrValue->context_id = $cid;
                    $attrValue->attribute_id = $aid;
                }
                $attrValue->lasteditor = $user['name'];
                if(is_object($jsonArr)) {
                    if($datatype == 'context') {
                        $attrValue->context_val = $jsonArr->id;
                    } else {
                        $attrValue->json_val = json_encode($jsonArr);
                    }
                } else {
                    if($datatype == 'geography') {
                        $parsed = $this->parseWkt($value);
                        if($parsed !== -1) {
                            $attrValue->geography_val = $parsed;
                        }
                    } else if($datatype == 'date') {
                        $attrValue->dt_val = $value;
                    } else {
                        $attrValue->str_val = $value;
                    }
                }
                $attrValue->save();
            }
        }
    }
}
