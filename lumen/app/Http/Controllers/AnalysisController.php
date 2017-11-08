<?php

namespace App\Http\Controllers;
use Log;
use App\User;
use App\Attribute;
use App\AvailableLayer;
use App\Context;
use Phaza\LaravelPostgis\Geometries\Point;
use \DB;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AnalysisController extends Controller {
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    // GET

    public function getAnalyses() {
        return response()->json(
            DB::table('stored_queries')
                ->get()
        );
    }

    // POST

    public function filterLayer($id, Request $request) {
        try {
            $layer = AvailableLayer::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This layer does not exist.'
            ]);
        }
        if(!isset($layer->context_type_id)) {
            return response()->json([
                'error' => 'This layer is not linked to a contexttype.'
            ]);
        }
        $ctid = $layer->context_type_id;
        $filters = json_decode($request->input('filters', '[]'));
        $columns = json_decode($request->input('columns', '[]'));
        $query = $this->filter([$ctid], $filters, $columns);
        return response()->json($query->get());
    }

    public function filterContexts(Request $request) {
        $contextTypes = json_decode($request->get('contextTypes', '[]'));
        $filters = json_decode($request->input('filters', '[]'));
        $columns = json_decode($request->input('columns', '[]'));
        $query = $this->filter($contextTypes, $filters, $columns);
        $rows = $query->get();
        $results = [];
        foreach($rows as $r) {
            $column = $this->getAttributeColumn($r->attribute_id);
            if(!isset($results[$r->index])) {
                $results[$r->index] = [
                    'name' => $r->name,
                    'context_id' => $r->index,
                    'root_context_id' => $r->root_context_id,
                    'rank' => $r->rank,
                    'geom' => $r->geom,
                    'color' => $r->color
                ];

                if($column !== null) {
                    $results[$r->index]['attributes'] = [
                        [
                            'id' => $r->attribute_id,
                            'value' => $r->{$column},
                            'datatype' => $column
                        ]
                    ];
                }

                if(isset($r->geom)) {
                    $filteredValues = [];
                    for($i=0; $i<count($filters); $i++) {
                        $f = $filters[$i];
                        if(isset($f->func)) {
                            $value = "'$r->geom'";
                            $raw = $this->getAsRaw($f->func, $value, $f->values);
                            $filteredValues[$i] = DB::select("SELECT $raw")[0];
                        }
                    }
                    $results[$r->index]['filters'] = $filteredValues;
                }
            } else {
                if($column !== null) {
                    $results[$r->index]['attributes'][] = [
                        'id' => $r->attribute_id,
                        'value' => $r->{$column},
                        'datatype' => $column
                    ];
                }
            }
        }
        return response()->json($results);
    }

    // PATCH

    // PUT

    // DELETE

    // OTHER FUNCTIONS

    private function filter($types, $filters, $columns) {
        if(!isset($types) || empty($types)) {
            $query = Context::query();
        } else {
            $query = Context::whereIn('context_type_id', $types);
        }
        $query->leftJoin('attribute_values', 'contexts.id', '=', 'attribute_values.context_id');
        $query->leftJoin('geodata', 'geodata.id', '=', 'contexts.geodata_id');
        if(isset($filters)) {
            $this->applyFilter($query, $filters[0], true);
            for($i=1; $i<count($filters); $i++) {
                $this->applyFilter($query, $filters[$i], false);
            }
        }
        // we need the context id to filter results by context id
        $query->addSelect('contexts.id AS index');
        if(empty($columns)) {
            $query->addSelect('*');
        } else {
            foreach($columns as $c) {
                $query->addSelect($c);
            }
        }
        return $query;
    }

    private function applyFilter($query, $filter, $and = false) {
        if(!$this->isValidCompare($filter->comp)) return;
        if(isset($filter->id)) {
            if($and) {
                $query->where(function($q) use($query, $filter) {
                    $q->where('attribute_values.attribute_id', $filter->id);
                    if(isset($filter->func)) {
                        $f = $filter->func;
                        if(!$this->isValidFunction($f)) {
                            // TODO
                            return;
                        }
                        $col = $this->getAttributeColumn($filter->id);
                        $raw = $this->getAsRaw($f, $col, $filter->values);
                        $q->where($raw, $filter->comp, $filter->values[0]);
                    } else {
                        $q->where($this->getAttributeColumn($filter->id), $filter->comp, $filter->values[0]);
                    }
                });
            } else {
                $query->orWhere(function($q) use($query, $filter) {
                    $q->where('attribute_values.attribute_id', $filter->id);
                    if(isset($filter->func)) {
                        $f = $filter->func;
                        if(!$this->isValidFunction($f)) {
                            // TODO
                            return;
                        }
                        $col = $this->getAttributeColumn($filter->id);
                        $raw = $this->getAsRaw($f, $col, $filter->values);
                        $q->where($raw, $filter->comp, $filter->values[0]);
                    } else {
                        $q->where($this->getAttributeColumn($filter->id), $filter->comp, $filter->values[0]);
                    }
                });
            }
        } else if(isset($filter->func)) {
            $raw = $this->getAsRaw($filter->func, 'geom', $filter->values);
            if($and) {
                $query->where($raw, $filter->comp, $filter->values[0]);
            } else {
                $query->orWhere($raw, $filter->comp, $filter->values[0]);
            }
        }
    }

    private function isValidCompare($comp) {
        switch($comp) {
            case '=':
            case '<>':
            case '!=':
            case '>':
            case '>=':
            case '<':
            case '<=':
            case 'like':
            case 'ilike':
                return true;
            default:
                return false;
        }
    }

    private function isValidFunction($func) {
        switch($func) {
            case 'distance':
            case 'area':
                return true;
            default:
                return false;
        }
    }

    private function getAsRaw($func, $column, $values) {
        switch($func) {
            case 'distance':
                $pos = $values[1];
                $point = new Point($pos[0], $pos[1]);
                $wkt = $point->toWKT();
                return DB::raw("ST_Distance($column, ST_GeogFromText('$wkt'), true)");
            case 'area':
                // return area as sqm, sqm should be default for SRID 4326
                return DB::raw("ST_Area($column, true)");
        }
    }

    private function getAttributeColumn($aid) {
        try {
            $attr = Attribute::findOrFail($aid);
        } catch(ModelNotFoundException $e) {
            return null;
        }
        $datatype = $attr->datatype;
        switch($datatype) {
            case 'string':
            case 'stringf':
            case 'list':
            	return 'str_val';
            case 'double':
            	return 'dbl_val';
            case 'string-sc':
            case 'string-mc':
            	return 'thesaurus_val';
            case 'epoch':
            	return 'json_val';
            case 'date':
            	return 'dt_val';
            case 'dimension':
            	return 'json_val';
            case 'geography':
            	return 'geography_val';
            case 'integer':
            case 'boolean':
            case 'percentage':
            	return 'int_val';
            case 'context':
            	return 'context_val';
            default:
                return 'str_val';
        }

    }
}
