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
use App\File;
use App\Source;
use App\Literature;
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
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ContextController extends Controller {
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    // GET

    public function getContexts() {
        $user = \Auth::user();
        if(!$user->can('view_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }

        $contextEntries = ContextType::join('contexts', 'contexts.context_type_id', '=', 'context_types.id')->select('contexts.*', 'type', 'thesaurus_url as uri')->orderBy('rank')->get();

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

        return response()->json($response);
    }

    public function getContextTypeAttributes($id) {
        return response()->json(
            DB::table('context_types as c')
            ->where('c.id', $id)
            ->join('context_attributes as ca', 'c.id', '=', 'ca.context_type_id')
            ->join('attributes as a', 'ca.attribute_id', '=', 'a.id')
            ->orderBy('ca.position', 'asc')
            ->get()
        );
    }

    public function getAttributes() {
        return Attribute::all();
    }

    public function getContextTypes() {
        return ContextType::all();
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

    public function getDropdownOptions() {
        $user = \Auth::user();
        if(!$user->can('view_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $attrs = Attribute::whereIn('datatype', ['string-sc', 'string-mc', 'epoch'])->get();
        $choices = [];
        foreach($attrs as &$attr) {
            $rootId = ThConcept::where('concept_url', $attr->thesaurus_root_url)->value('id');
            if(!isset($rootId)) continue;
            $choices[$attr->id] = DB::select("
                WITH RECURSIVE
                top AS (
                    SELECT br.broader_id, br.narrower_id, c.concept_url
                    FROM th_broaders br
                    JOIN th_concept as c on c.id = br.narrower_id
                    WHERE broader_id = $rootId
                    UNION
                    SELECT br.broader_id, br.narrower_id, c2.concept_url
                    FROM top t, th_broaders br
                    JOIN th_concept as c2 on c2.id = br.narrower_id
                    WHERE t.narrower_id = br.broader_id
                )
                SELECT *
                FROM top
            ");
        }
        return response()->json($choices);
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

    public function getAvailableAttributeTypes() {
        return response()->json([
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
                'datatype' => 'boolean',
                'description' => 'attribute.boolean.desc'
            ],
            [
                'datatype' => 'percentage',
                'description' => 'attribute.percentage.desc'
            ],
            [
                'datatype' => 'context',
                'description' => 'attribute.context.desc'
            ]
        ]);
    }

    public function searchContextName($term) {
        $user = \Auth::user();
        if(!$user->can('view_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $term = urldecode($term);
        $matchingContexts = Context::where('name', 'ilike', '%'.$term.'%')
            ->select('name', 'id')
            ->orderBy('name')
            ->get();
        return response()->json($matchingContexts);
    }

    public function searchGlobal($term, $lang) {
        $user = \Auth::user();
        if(!$user->can('view_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $matches = [];
        // decode encoded search term
        $term = urldecode($term);
        $likeTerm = '%' . $term . '%';

        // get contexts where the name, last editor or date matches the search term
        $matchingContexts = Context::where('name', 'ilike', $likeTerm)
            ->orWhere('lasteditor', 'ilike', $likeTerm)
            ->orWhere(DB::raw('to_char(updated_at, \'MM.DD.YYYY TMday TMmonth\')'), 'ilike', $likeTerm)
            ->select('name', 'id', 'updated_at', DB::raw('to_char(updated_at, \'MM.DD.YYYY TMday TMmonth\') as updated_str'))
            ->get();
        foreach($matchingContexts as $c) {
            $type = 'context';
            $key = $type . "_" . $c->id;
            if(!isset($matches[$key])) {
                $count = 1;
                $matches[$key] = [
                    'id' => $c->id,
                    'name' => $c->name,
                    'type' => $type,
                    'count' => $count,
                    'values' => []
                ];
            } else {
                $matches[$key]['count']++;
            }
            if(isset($c->updated_str) && stripos($c->updated_str, $term) !== false) {
                $matches[$key]['updated_at'] = $c->updated_at;
            }
        }

        // get contexts where the attribute label matches the search term
        $matchingContexts = Context::where(function($query) use ($likeTerm, $lang) {
            $query->where('thl.short_name', $lang)
                ->where('thcl.label', 'ilike', $likeTerm);
        })
            ->join('context_attributes as ca', 'ca.context_type_id', '=', 'contexts.context_type_id')
            ->join('attributes as a', 'a.id', '=', 'ca.attribute_id')
            ->leftJoin('attribute_values as av', function($query) {
                $query->on('a.id', '=', 'av.attribute_id')
                    ->on('contexts.id', '=', 'av.context_id');
            })
            ->leftJoin('th_concept as thc', 'a.thesaurus_url', '=', 'thc.concept_url')
            ->leftJoin('th_concept_label as thcl', 'thc.id', '=', 'thcl.concept_id')
            ->leftJoin('th_language as thl', 'thcl.language_id', '=', 'thl.id')
            ->select('name', 'contexts.id as cid', 'a.thesaurus_url', 'av.*', DB::raw('ST_AsText(geography_val) as geography_val_wkt'))
            ->distinct()
            ->get();
        foreach($matchingContexts as $c) {
            $type = 'context';
            $key = $type . "_" . $c->cid;
            if(!isset($matches[$key])) {
                $count = 1;
                $matches[$key] = [
                    'id' => $c->cid,
                    'name' => $c->name,
                    'type' => $type,
                    'count' => $count,
                    'values' => []
                ];
            } else {
                $matches[$key]['count']++;
            }
            $value = null;
            if(isset($c->str_val)) {
                $value = $c->str_val;
            } else if(isset($c->int_val)) {
                $value = $c->int_val;
            } else if(isset($c->dbl_val)) {
                $value = $c->dbl_val;
            } else if(isset($c->thesaurus_val)) {
                $value = $c->thesaurus_val;
            } else if(isset($c->json_val)) {
                $value = json_decode($c->json_val);
            } else if(isset($c->geography_val)) {
                $value = $c->geography_val_wkt;
            } else if(isset($c->dt_val)) {
                $value = $c->dt_val;
            }
            if(isset($value)) $matches[$key]['values'][$c->thesaurus_url] = $value;
        }

        // get contexts where the context type label matches the search term
        $matchingContexts = Context::where(function($query) use ($likeTerm, $lang) {
            $query->where('thl.short_name', $lang)
                ->where('thcl.label', 'ilike', $likeTerm);
        })
            ->join('context_types as ct', 'ct.id', '=', 'contexts.context_type_id')
            ->leftJoin('th_concept as thc', 'ct.thesaurus_url', '=', 'thc.concept_url')
            ->leftJoin('th_concept_label as thcl', 'thc.id', '=', 'thcl.concept_id')
            ->leftJoin('th_language as thl', 'thcl.language_id', '=', 'thl.id')
            ->select('name', 'contexts.id', 'ct.thesaurus_url')
            ->distinct()
            ->get();
        foreach($matchingContexts as $c) {
            $type = 'context';
            $key = $type . "_" . $c->id;
            if(!isset($matches[$key])) {
                $count = 1;
                $matches[$key] = [
                    'id' => $c->id,
                    'name' => $c->name,
                    'type' => $type,
                    'count' => $count,
                    'values' => []
                ];
            } else {
                $matches[$key]['count']++;
            }
            $matches[$key]['context_type'] = $c->thesaurus_url;
        }

        $matchingFiles = File::where('name', 'ilike', $likeTerm)
            ->orWhere('copyright', 'ilike', $likeTerm)
            ->orWhere('description', 'ilike', $likeTerm)
            ->orWhere('photos.lasteditor', 'ilike', $likeTerm)
            ->orWhere(function($query) use ($likeTerm, $lang) {
                $query->where('thl.short_name', $lang)
                    ->where('thcl.label', 'ilike', $likeTerm);
            })
            ->leftJoin('photo_tags as tags', 'photos.id', '=', 'tags.photo_id')
            ->leftJoin('th_concept as thc', 'tags.concept_url', '=', 'thc.concept_url')
            ->leftJoin('th_concept_label as thcl', 'thc.id', '=', 'thcl.concept_id')
            ->leftJoin('th_language as thl', 'thcl.language_id', '=', 'thl.id')
            ->select('name', 'photos.id', 'thumb', 'mime_type', 'copyright', 'description', 'thcl.label')
            ->orderBy('name')
            ->get();
        foreach($matchingFiles as $f) {
            $type = 'file';
            $key = $type . "_" . $f->id;
            if(!isset($matches[$key])) {
                $count = 1;
                $matching_values = [];
                if(isset($f->copyright) && stripos($f->copyright, $term) !== false) {
                    $matching_values['copyright'] = $f->copyright;
                }
                if(isset($f->description) && stripos($f->description, $term) !== false) {
                    $matching_values['description'] = $f->description;
                }
                $matches[$key] = [
                    'id' => $f->id,
                    'name' => $f->name,
                    'type' => $type,
                    'count' => $count,
                    'values' => $matching_values,
                    'tags' => []
                ];
                if(isset($f->thumb) && substr($f->mime_type, 0, 6) === 'image/') {
                    $thumb_url = Storage::disk('public')->url(env('SP_FILE_PATH') .'/'. $f->thumb);
                    $matches[$key]['thumb_url'] = $thumb_url;
                }
            } else {
                $matches[$key]['count']++;
            }
            if(isset($f->label) && stripos($f->label, $term) !== false) {
                $matches[$key]['tags'][] = $f->label;
            }
        }

        $matchingLayers = AvailableLayer::where('name', 'ilike', $likeTerm)
            ->orWhere('url', 'ilike', $likeTerm)
            ->select('name', 'id', 'url')
            ->orderBy('name')
            ->get();
        foreach($matchingLayers as $l) {
            $type = 'layer';
            $key = $type . "_" . $l->id;
            if(!isset($matches[$key])) {
                $count = 1;
                $matching_values = [];
                if(isset($l->url) && stripos($l->url, $term) !== false) {
                    $matching_values['url'] = $l->url;
                }
                $matches[$key] = [
                    'id' => $lid,
                    'name' => $l->name,
                    'type' => $type,
                    'count' => $count,
                    'values' => $matching_values
                ];
            } else {
                $matches[$key]['count']++;
            }
        }

        $matchingValues = AttributeValue::where('str_val', 'ilike', $likeTerm)
            ->orWhere(DB::raw('CAST (int_val AS text)'), 'ilike', $likeTerm)
            ->orWhere(DB::raw('CAST (dbl_val AS text)'), 'ilike', $likeTerm)
            ->orWhere('possibility_description', 'ilike', $likeTerm)
            ->orWhere(DB::raw('CAST (json_val AS text)'), 'ilike', $likeTerm)
            ->orWhere(DB::raw('ST_AsText(geography_val)'), 'ilike', $likeTerm)
            // use server's locale for day and month (TM). To use a different locale `SET lc_time = <supported locale>` has to be executed before the actual query
            ->orWhere(DB::raw('to_char(dt_val, \'MM.DD.YYYY TMday TMmonth\')'), 'ilike', $likeTerm)
            ->orWhere('attribute_values.lasteditor', 'ilike', $likeTerm)
            ->orWhere(function($query) use ($likeTerm, $lang) {
                $query->where('thl.short_name', $lang)
                    ->where('thcl.label', 'ilike', $likeTerm);
            })
            ->orWhere('c2.name', 'ilike', $likeTerm)
            ->join('contexts as c', 'context_id', '=', 'c.id')
            ->join('attributes', 'attribute_id', '=', 'attributes.id')
            ->leftJoin('contexts as c2', 'c2.id', '=', 'context_val')
            ->leftJoin('th_concept as thc', 'thesaurus_val', '=', 'thc.concept_url')
            ->leftJoin('th_concept_label as thcl', 'thc.id', '=', 'thcl.concept_id')
            ->leftJoin('th_language as thl', 'thcl.language_id', '=', 'thl.id')
            ->select('c.id', 'c.name', 'str_val', 'int_val', 'dbl_val', 'thesaurus_val', 'possibility_description', 'json_val', DB::raw('ST_AsText(geography_val) as geography_val'), 'dt_val', 'attributes.thesaurus_url', 'context_val', 'c2.name as context_val_name', 'label', DB::raw('to_char(dt_val, \'MM.DD.YYYY TMday TMmonth\') as dt_val_str'))
            ->orderBy('c.name')
            ->distinct()
            ->get();

        foreach($matchingValues as $v) {
            $type = 'context';
            $key = $type . "_" . $v->id;
            if(!isset($matches[$key])) {
                $count = 1;
                $matches[$key] = [
                    'id' => $v->id,
                    'name' => $v->name,
                    'type' => $type,
                    'count' => $count,
                    'values' => []
                ];
            } else {
                $matches[$key]['count']++;
            }
            $value = null;
            if(isset($v->str_val) && stripos($v->str_val, $term) !== false) {
                $value = $v->str_val;
            } else if(isset($v->int_val) && stripos($v->int_val, $term) !== false) {
                $value = $v->int_val;
            } else if(isset($v->dbl_val) && stripos($v->dbl_val, $term) !== false) {
                $value = $v->dbl_val;
            } else if(isset($v->thesaurus_val) && stripos($v->label, $term) !== false) {
                $value = $v->thesaurus_val;
            } else if(isset($v->possibility_description) && stripos($v->possibility_description, $term) !== false) {
                $value = $v->possibility_description;
            } else if(isset($v->json_val) && stripos($v->json_val, $term) !== false) {
                $value = json_decode($v->json_val);
            } else if(isset($v->geography_val) && stripos($v->geography_val, $term) !== false) {
                $value = $v->geography_val;
            } else if(isset($v->dt_val) && stripos($v->dt_val_str, $term) !== false) {
                $value = $v->dt_val;
            } else if(isset($v->context_val) && stripos($v->context_val_name, $term) !== false) {
                $value = $v->context_val_name;
            }
            if(isset($value)) $matches[$key]['values'][$v->thesaurus_url] = $value;
        }

        $matchingSources = Source::where('description', 'ilike', $likeTerm)
            ->orWhere('sources.lasteditor', 'ilike', $likeTerm)
            ->orWhere('contexts.lasteditor', 'ilike', $likeTerm)
            ->orWhere('literature.lasteditor', 'ilike', $likeTerm)
            ->join('contexts', 'context_id', '=', 'contexts.id')
            ->join('attributes', 'attribute_id', '=', 'attributes.id')
            ->join('literature', 'literature_id', '=', 'literature.id')
            ->select('contexts.id', 'attributes.id as attribute_id', 'literature.id as literature_id', 'name', 'description', 'attributes.thesaurus_url', 'literature.title')
            ->orderBy('name')
            ->get();
        foreach($matchingSources as $s) {
            $type = 'context';
            $key = $type . "_" . $s->id;
            if(!isset($matches[$key])) {
                $count = 1;
                $matches[$key] = [
                    'id' => $s->id,
                    'aid' => $s->attribute_id,
                    'name' => $s->name,
                    'type' => $type,
                    'count' => $count,
                    'values' => [],
                    'sources' => []
                ];
            } else {
                $matches[$key]['count']++;
            }
            $matches[$key]['sources'][] = [
                'title' => $s->title,
                'desc' => $s->description
            ];
        }

        $matchingBibliography = Literature::where('author', 'ilike', $likeTerm)
            ->orWhere('editor', 'ilike', $likeTerm)
            ->orWhere('title', 'ilike', $likeTerm)
            ->orWhere('journal', 'ilike', $likeTerm)
            ->orWhere('year', 'ilike', $likeTerm)
            ->orWhere('pages', 'ilike', $likeTerm)
            ->orWhere('volume', 'ilike', $likeTerm)
            ->orWhere('number', 'ilike', $likeTerm)
            ->orWhere('booktitle', 'ilike', $likeTerm)
            ->orWhere('publisher', 'ilike', $likeTerm)
            ->orWhere('address', 'ilike', $likeTerm)
            ->orWhere('misc', 'ilike', $likeTerm)
            ->orWhere('howpublished', 'ilike', $likeTerm)
            ->orWhere('type', 'ilike', $likeTerm)
            ->orWhere('annote', 'ilike', $likeTerm)
            ->orWhere('chapter', 'ilike', $likeTerm)
            ->orWhere('crossref', 'ilike', $likeTerm)
            ->orWhere('edition', 'ilike', $likeTerm)
            ->orWhere('institution', 'ilike', $likeTerm)
            ->orWhere('key', 'ilike', $likeTerm)
            ->orWhere('month', 'ilike', $likeTerm)
            ->orWhere('note', 'ilike', $likeTerm)
            ->orWhere('organization', 'ilike', $likeTerm)
            ->orWhere('school', 'ilike', $likeTerm)
            ->orWhere('series', 'ilike', $likeTerm)
            ->select('id', 'author', 'editor', 'title', 'journal', 'year', 'pages', 'volume', 'number', 'booktitle', 'publisher', 'address', 'misc', 'howpublished', 'type', 'annote', 'chapter', 'crossref', 'edition', 'institution', 'key', 'month', 'note', 'organization', 'school', 'series', 'lasteditor')
            ->orderBy('title')
            ->get();

        foreach($matchingBibliography as $b) {
            $type = 'bibliography';
            $key = $type . "_" . $b->id;
            if(!isset($matches[$key])) {
                $count = 1;
                $matches[$key] = [
                    'id' => $b->id,
                    'name' => $b->title,
                    'type' => $type,
                    'count' => $count,
                    'values' => []
                ];
            }
            if(isset($b->author) && stripos($b->author, $term) !== false) {
            	$matches[$key]['values']['author'] = $b->author;
                $matches[$key]['count']++;
            }
            if(isset($b->editor) && stripos($b->editor, $term) !== false) {
            	$matches[$key]['values']['editor'] = $b->editor;
                $matches[$key]['count']++;
            }
            if(isset($b->title) && stripos($b->title, $term) !== false) {
            	$matches[$key]['values']['title'] = $b->title;
                $matches[$key]['count']++;
            }
            if(isset($b->journal) && stripos($b->journal, $term) !== false) {
            	$matches[$key]['values']['journal'] = $b->journal;
                $matches[$key]['count']++;
            }
            if(isset($b->year) && stripos($b->year, $term) !== false) {
            	$matches[$key]['values']['year'] = $b->year;
                $matches[$key]['count']++;
            }
            if(isset($b->pages) && stripos($b->pages, $term) !== false) {
            	$matches[$key]['values']['pages'] = $b->pages;
                $matches[$key]['count']++;
            }
            if(isset($b->volume) && stripos($b->volume, $term) !== false) {
            	$matches[$key]['values']['volume'] = $b->volume;
                $matches[$key]['count']++;
            }
            if(isset($b->number) && stripos($b->number, $term) !== false) {
            	$matches[$key]['values']['number'] = $b->number;
                $matches[$key]['count']++;
            }
            if(isset($b->booktitle) && stripos($b->booktitle, $term) !== false) {
            	$matches[$key]['values']['booktitle'] = $b->booktitle;
                $matches[$key]['count']++;
            }
            if(isset($b->publisher) && stripos($b->publisher, $term) !== false) {
            	$matches[$key]['values']['publisher'] = $b->publisher;
                $matches[$key]['count']++;
            }
            if(isset($b->address) && stripos($b->address, $term) !== false) {
            	$matches[$key]['values']['address'] = $b->address;
                $matches[$key]['count']++;
            }
            if(isset($b->misc) && stripos($b->misc, $term) !== false) {
            	$matches[$key]['values']['misc'] = $b->misc;
                $matches[$key]['count']++;
            }
            if(isset($b->howpublished) && stripos($b->howpublished, $term) !== false) {
            	$matches[$key]['values']['howpublished'] = $b->howpublished;
                $matches[$key]['count']++;
            }
            if(isset($b->type) && stripos($b->type, $term) !== false) {
            	$matches[$key]['values']['type'] = $b->type;
                $matches[$key]['count']++;
            }
            if(isset($b->annote) && stripos($b->annote, $term) !== false) {
            	$matches[$key]['values']['annote'] = $b->annote;
                $matches[$key]['count']++;
            }
            if(isset($b->chapter) && stripos($b->chapter, $term) !== false) {
            	$matches[$key]['values']['chapter'] = $b->chapter;
                $matches[$key]['count']++;
            }
            if(isset($b->crossref) && stripos($b->crossref, $term) !== false) {
            	$matches[$key]['values']['crossref'] = $b->crossref;
                $matches[$key]['count']++;
            }
            if(isset($b->edition) && stripos($b->edition, $term) !== false) {
            	$matches[$key]['values']['edition'] = $b->edition;
                $matches[$key]['count']++;
            }
            if(isset($b->institution) && stripos($b->institution, $term) !== false) {
            	$matches[$key]['values']['institution'] = $b->institution;
                $matches[$key]['count']++;
            }
            if(isset($b->key) && stripos($b->key, $term) !== false) {
            	$matches[$key]['values']['key'] = $b->key;
                $matches[$key]['count']++;
            }
            if(isset($b->month) && stripos($b->month, $term) !== false) {
            	$matches[$key]['values']['month'] = $b->month;
                $matches[$key]['count']++;
            }
            if(isset($b->note) && stripos($b->note, $term) !== false) {
            	$matches[$key]['values']['note'] = $b->note;
                $matches[$key]['count']++;
            }
            if(isset($b->organization) && stripos($b->organization, $term) !== false) {
            	$matches[$key]['values']['organization'] = $b->organization;
                $matches[$key]['count']++;
            }
            if(isset($b->school) && stripos($b->school, $term) !== false) {
            	$matches[$key]['values']['school'] = $b->school;
                $matches[$key]['count']++;
            }
            if(isset($b->series) && stripos($b->series, $term) !== false) {
            	$matches[$key]['values']['series'] = $b->series;
                $matches[$key]['count']++;
            }
        }

        $matchingUsers = User::where('name', 'ilike', $likeTerm)
            ->orWhere('email', 'ilike', $likeTerm)
            ->select('name', 'email', 'id')
            ->orderBy('name')
            ->get();
        foreach($matchingUsers as $u) {
            $type = 'user';
            $key = $type . "_" . $u->id;
            if(!isset($matches[$key])) {
                $count = 1;
                $matches[$key] = [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'type' => $type,
                    'count' => $count,
                    'values' => []
                ];
            } else {
                $matches[$key]['count']++;
            }
        }

        usort($matches, ['App\Helpers', 'sortMatchesDesc']);

        return response()->json($matches);
    }

    // POST

    public function add(Request $request) {
        $user = \Auth::user();
        if(!$user->can('create_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $this->validate($request, Context::rules);

        $context = new Context();
        $rank;
        if($request->has('root_context_id')) {
            $rank = Context::where('root_context_id', '=', $request->get('root_context_id'))->max('rank') + 1;
        } else {
            $rank = Context::whereNull('root_context_id')->max('rank') + 1;
        }
        $context->rank = $rank;

        foreach($request->intersect(array_keys(Context::rules)) as $key => $value) {
            $context->{$key} = $value;
        }
        $context->lasteditor = $user['name'];
        $context->save();

        return response()->json([
            'context' => ContextType::join('contexts',
                    'contexts.context_type_id', '=', 'context_types.id'
                )
                ->select('contexts.*', 'type', 'thesaurus_url as uri')
                ->where('contexts.id', $context->id)
                ->orderBy('rank')
                ->first()
        ]);
    }

    public function duplicate(Request $request, $id) {
        $user = \Auth::user();
        if(!$user->can('duplicate_edit_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }

        try {
            $toDuplicate = Context::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This context does not exist'
            ]);
        }

        $newDuplicate = $toDuplicate->replicate([
            'geodata_id'
        ]);
        $newDuplicate->geodata_id = null;
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
        $newDuplicate->lasteditor = $user['name'];
        $newDuplicate->save();
        $toDuplicateValues = AttributeValue::where('context_id', $id)
            ->get();
        foreach($toDuplicateValues as $value) {
            $newValue = $value->replicate();
            $newValue->context_id = $newDuplicate->id;
            $newValue->save();
        }
        $additionalProps = ContextType::where('id', $toDuplicate->context_type_id)->select('type', 'thesaurus_url as uri')->first();
        $newDuplicate->type = $additionalProps->type;
        $newDuplicate->uri = $additionalProps->uri;
        return response()->json(['obj' => $newDuplicate]);
    }

    // PATCH

    public function patchRank(Request $request, $id) {
        $user = \Auth::user();
        if(!$user->can('duplicate_edit_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }

        $this->validate($request, [
            'rank' => 'required|integer',
            'parent_id' => 'nullable|integer|exists:contexts,id',
        ]);

        try {
            $context = Context::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This context does not exist'
            ]);
        }

        $rank = $request->get('rank');
        $hasParent = $request->has('parent_id');
        $oldRank = $context->rank;
        $context->rank = $rank;
        $context->lasteditor = $user['name'];

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

    public function linkGeodata(Request $request, $cid, $gid = null) {
        $user = \Auth::user();
        if(!$user->can('link_geodata')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }

        try {
            $context = Context::findOrFail($cid);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This context does not exist'
            ]);
        }
        if ($gid === null) { // if only cid is given, unlink the geodata of that context
            $context->geodata_id = null;
        } else {
            if(isset($context->geodata_id)) {
                return response()->json([
                    'error' => 'This context is already linked to a geodata'
                ]);
            }
            try {
                $geodata = Geodata::findOrFail($gid);
            } catch(ModelNotFoundException $e) {
                return response()->json([
                    'error' => 'This geodata does not exist'
                ]);
            }
            $layer = AvailableLayer::where('context_type_id', '=', $context->context_type_id)->first();

            $undefinedError = [
                'error' => 'Layer Type doesn\'t match Geodata Type'
            ];
            $matching = false;
            if(($geodata->geom instanceof Polygon || $geodata->geom instanceof MultiPolygon) && Helpers::endsWith($layer->type, 'Polygon')) {
                $matching = true;
            } else if(($geodata->geom instanceof LineString || $geodata->geom instanceof MultiLineString) && Helpers::endsWith($layer->type, 'Linestring')) {
                $matching = true;
            } else if(($geodata->geom instanceof Point || $geodata->geom instanceof MultiPoint) && Helpers::endsWith($layer->type, 'Point')) {
                $matching = true;
            }
            if(!$matching) return response()->json($undefinedError);
            $context->geodata_id = $gid;
        }
        $context->lasteditor = $user['name'];
        $context->save();
        return response()->json([
            'context' => $context
        ]);
    }

    // PUT

    public function put(Request $request, $id){
        $user = \Auth::user();

        if(!$user->can('duplicate_edit_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }

        $this->validate($request, Context::patchRules);

        try {
            $context = Context::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This context does not exist'
            ]);
        }

        foreach($request->intersect(array_keys(Context::patchRules)) as $key => $value) {
            $context->{$key} = $value;
        }
        $context->lasteditor = $user['name'];
        $context->save();

        $this->updateOrInsert($request->except(array_keys(Context::patchRules)), $id, $user);

        return response()->json(['context' => $context]);
    }

    public function putPossibility(Request $request, $cid, $aid) {
        $this->validate($request, [
            'possibility' => 'required|nullable|integer',
            'possibility_description' => 'required|nullable|string'
        ]);

        $user = \Auth::user();
        if(!$user->can('duplicate_edit_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $possibility = $request->get('possibility');
        $description = $request->get('possibility_description');

        $av = AttributeValue::where([
                ['context_id', '=', $cid],
                ['attribute_id', '=', $aid]
            ])->first();
        if($av === null) { //insert
            $av = new AttributeValue();

            $av->context_id = $cid;
            $av->attribute_id = $aid;
            $av->possibility = $possibility;
            $av->possibility_description = $description;
            $av->lasteditor = $user['name'];

            $av->save();
        } else { //update
            $av->possibility = $possibility;
            $av->possibility_description = $description;
            $av->lasteditor = $user['name'];

            $av->save();
        }
        return response()->json($av);
    }

    // DELETE

    public function delete($id) {
        $user = \Auth::user();
        if(!$user->can('delete_move_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }

        try {
            $context = Context::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This context does not exist'
            ]);
        }

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

    // EDITOR FUNCTIONS

    // GET

    public function getOccurrenceCount($id) {
        $user = \Auth::user();
        if(!$user->can('view_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }

        $cnt = Context::where('context_type_id', '=', $id)->count();
        return response()->json([
            'count' => $cnt
        ]);
    }

    public function searchForLabel($label, $lang = 'de') {
        $user = \Auth::user();
        if(!$user->can('view_concepts_th')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }

        if($label == null) return response()->json();

        $matchedConcepts = DB::table('th_concept_label as l')
            ->select('c.concept_url', 'c.id', 'l.label')
            ->join('th_concept as c', 'c.id', '=', 'l.concept_id')
            ->join('th_language as lng', 'l.language_id', '=', 'lng.id')
            ->where([
                ['label', 'ilike', '%' . $label . '%'],
                ['lng.short_name', '=', $lang]
            ])
            ->groupBy('c.id', 'l.label')
            ->orderBy('l.label')
            ->get();
        return response()->json($matchedConcepts);
    }

    // POST

    public function addContextType(Request $request) {
        $user = \Auth::user();
        if(!$user->can('view_concepts')) {//TODO wrong permission
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }

        $this->validate($request, [
            'concept_url' => 'required|url|exists:th_concept',
            'type' => 'required|integer|between:0,1',
            'geomtype' => 'required|geom_type'
        ]);

        $curl = $request->get('concept_url');
        $type = $request->get('type');
        $geomtype = $request->get('geomtype');
        $cType = new ContextType();
        $cType->thesaurus_url = $curl;
        $cType->type = $type;
        $cType->save();

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
            'contexttype' => $cType
        ]);
    }

    public function addAttributeToContextType(Request $request, $ctid) {
        $user = \Auth::user();
        if(!$user->can('view_concepts')) {//TODO: wrong permission
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }

        $this->validate($request, [
            'attribute_id' => 'required|integer|exists:attributes,id'
        ]);

        $aid = $request->get('attribute_id');
        $attrsCnt = ContextAttribute::where('context_type_id', '=', $ctid)->count();
        $ca = new ContextAttribute();
        $ca->context_type_id = $ctid;
        $ca->attribute_id = $aid;
        $ca->position = $attrsCnt + 1; // add new attribute to the end
        $ca->save();

        $a = Attribute::find($aid);
        $ca->datatype = $a->datatype;

        return response()->json([
            'attribute' => DB::table('context_types as c')
                ->where('ca.id', $ca->id)
                ->join('context_attributes as ca', 'c.id', '=', 'ca.context_type_id')
                ->join('attributes as a', 'ca.attribute_id', '=', 'a.id')
                ->first()
        ]);
    }

    public function addAttribute(Request $request) {
        $user = \Auth::user();
        if(!$user->can('view_concepts')) {//TODO: wrong permission
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }

        $this->validate($request, [
            'label_id' => 'required|integer|exists:th_concept,id',
            'datatype' => 'required|string',
            'parent_id' => 'nullable|integer|exists:th_concept,id'
        ]);

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

        return response()->json([
            'attribute' => $attr
        ]);
    }

    // PATCH

    public function editContextType(Request $request, $ctid) {
        $user = \Auth::user();
        if(!$user->can('view_concepts')) {//TODO wrong permission
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }

        $this->validate($request, [
            'new_url' => 'required|url|exists:th_concept,concept_url'
        ]);

        try {
            $ct = ContextType::findOrFail($ctid);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This context type does not exist'
            ]);
        }

        $newUrl = $request->get('new_url');
        $ct->thesaurus_url = $newUrl;
        $ct->save();
    }

    public function moveAttributeUp(Request $request, $ctid, $aid) {
        $user = \Auth::user();
        if(!$user->can('view_concepts')) {//TODO: wrong permission
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }

        $ca = ContextAttribute::where([
            ['attribute_id', '=', $aid],
            ['context_type_id', '=', $ctid]
        ])->first();

        if($ca === null){
            return response()->json([
                'error' => 'No ContextAttribute found'
            ]);
        }

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

    public function moveAttributeDown(Request $request, $ctid, $aid) {
        $user = \Auth::user();
        if(!$user->can('view_concepts')) {//TODO: wrong permission
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }

        $ca = ContextAttribute::where([
            ['attribute_id', '=', $aid],
            ['context_type_id', '=', $ctid]
        ])->first();

        if($ca === null){
            return response()->json([
                'error' => 'No ContextAttribute found'
            ]);
        }

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

    // DELETE

    public function deleteAttribute($id) {
        $user = \Auth::user();
        if(!$user->can('view_concepts')) {//TODO: wrong permission
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        Attribute::find($id)->delete();
    }

    public function deleteContextType($id) {
        $user = \Auth::user();
        if(!$user->can('view_concepts')) {//TODO: wrong permission
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        ContextType::find($id)->delete();
    }

    public function removeAttributeFromContextType($ctid, $aid) {
        $user = \Auth::user();
        if(!$user->can('view_concepts')) {//TODO: wrong permission
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $ca = ContextAttribute::where([
            ['attribute_id', '=', $aid],
            ['context_type_id', '=', $ctid]
        ])->first();

        if($ca === null){
            return response()->json([
                'error' => 'No ContextAttribute found'
            ]);
        }

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

    // OTHER FUNCTIONS

    private function getData($id) {
        $data = DB::table('attribute_values as av')->select('av.*', 'a.datatype', 'a.thesaurus_root_url')->join('attributes as a', 'av.attribute_id', '=', 'a.id')->where('context_id', $id)->get();
        foreach($data as &$attr) {
            if($attr->datatype == 'literature') {
                $attr->literature_info = DB::table('literature')->where('id', $attr->str_val)->first();
            } else if($attr->datatype == 'string-sc' || $attr->datatype == 'string-mc') {
                $attr->val = ThConcept::where('concept_url', $attr->thesaurus_val)->select('th_concept.id as narrower_id', 'concept_url')->first();
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
                    $attrVal['epoch'] = $jsonVal->epoch;
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

    private function updateOrInsert($values, $cid, $user) {
        foreach($values as $key => $value) {
            $ids = explode("_", $key);
            $aid = $ids[0];
            if($aid == "" || (isset($ids[1]) && $ids[1] != "")) continue;

            try {
                $datatype = Attribute::findOrFail($aid)->datatype;
            } catch(ModelNotFoundException $e) {
                continue;
            }
            // instead of checking for changes we delete all entries
            // to the given cid/aid pair and insert the one from the request again
            AttributeValue::where([
                ['context_id', $cid],
                ['attribute_id', $aid]
            ])->delete();

            // if a key exists but the value is null the attribute value was deleted
            if($value == 'null' || $value === null) {
                continue;
            }

            $jsonArr = json_decode($value);
            if($datatype === 'string-sc') $jsonArr = [$jsonArr]; //"convert" to array

            if($datatype === 'epoch' && is_object($jsonArr)) {
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

            //only string-sc, string-mc and list should be arrays
            if(is_array($jsonArr) && ($datatype == 'string-sc' || $datatype == 'string-mc' || $datatype == 'list')) {
                foreach($jsonArr as $v) {
                    $attr = new AttributeValue();
                    $attr->context_id = $cid;
                    $attr->attribute_id = $aid;
                    $attr->lasteditor = $user['name'];
                    if($datatype === 'list') {
                        $attr->str_val = $v->name;
                    } else {
                        try {
                            $set = ThConcept::findOrFail($v->narrower_id);
                            $attr->thesaurus_val = $set->concept_url;
                        } catch(ModelNotFoundException $e) {
                            continue;
                        }
                    }
                    $attr->save();
                }
            } else {
                $attrValue = new AttributeValue();
                $attrValue->context_id = $cid;
                $attrValue->attribute_id = $aid;
                $attrValue->lasteditor = $user['name'];
                if(is_object($jsonArr)) {
                    if($datatype == 'context') {
                        $attrValue->context_val = $jsonArr->id;
                    } else {
                        if($datatype == 'epoch') {
                            $jsonArr->epoch = [
                                'concept_url' => $jsonArr->epoch->concept_url
                            ];
                        }
                        $attrValue->json_val = json_encode($jsonArr);
                    }
                } else {
                    switch ($datatype) {
                        case 'geography':
                            $parsed = Helpers::parseWkt($value);
                            if($parsed !== -1) {
                                $attrValue->geography_val = $parsed;
                            }
                            break;
                        case 'date':
                            \Log::info($value);
                            $attrValue->dt_val = $value;
                            break;
                        case 'percentage':
                        case 'integer':
                            $attrValue->int_val = intval($value);
                            break;
                        case 'boolean':
                            $attrValue->int_val = ($value == 'true') ? 1 : 0;
                            break;
                        case 'double':
                            $attrValue->dbl_val = doubleval($value);
                            break;
                        default:
                            $attrValue->str_val = $value;
                    }
                }
                $attrValue->save();
            }
        }
    }
}
