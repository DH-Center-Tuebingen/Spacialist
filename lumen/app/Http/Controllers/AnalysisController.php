<?php

namespace App\Http\Controllers;
use Log;
use App\User;
use App\Attribute;
use App\AttributeValue;
use App\AvailableLayer;
use App\Context;
use App\File;
use App\Geodata;
use App\Literature;
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
        $orders = json_decode($request->input('orders', '[]'));
        $groups = json_decode($request->input('groups', '[]'));
        $limit = json_decode($request->input('limit', '{}'));
        $query = $this->filter([$ctid], $filters, $columns, $orders, $groups, $limit);
        return response()->json($query->get());
    }

    public function filterContexts(Request $request) {
        $origin = $request->input('origin');
        $filters = json_decode($request->input('filters', '[]'));
        $columns = json_decode($request->input('columns', '[]'));
        $orders = json_decode($request->input('orders', '[]'));
        $groups = json_decode($request->input('groups', '[]'));
        $limit = json_decode($request->input('limit', '{}'));
        $query = $this->filter($origin, $filters, $columns, $orders, $groups, $limit);
        $rows = $query->get();
        return response()->json($rows);
    }

    // PATCH

    // PUT

    // DELETE

    // OTHER FUNCTIONS

    private function filter($origin, $filters, $columns, $orders, $groups, $limit) {
        switch($origin) {
            case 'attribute_values':
                $query = AttributeValue::with([
                    'context_val',
                    'thesaurus_val'
                ]);
                break;
            case 'contexts':
                $query = Context::with([
                    'child_contexts',
                    'context_type',
                    'geodata',
                    'root_context',
                    'literatures',
                    'attributes',
                    'files'
                ]);
                break;
            case 'files':
                $query = File::with([
                    'contexts',
                    'tags'
                ]);
                break;
            case 'geodata':
                $query = Geodata::with([
                    'context'
                ]);
                break;
            case 'literature':
                $query = Literature::with([
                    'contexts'
                ]);
                break;
        }
        if(isset($filters)) {
            foreach($filters as $f) {
                $this->applyFilter($query, $f, $groups);
            }
        }

        if(isset($groups)) {
            foreach($groups as $g) {
                $query->groupBy($g);
            }
        }
        if(isset($orders)) {
            foreach($orders as $o) {
                $query->orderBy($o->col, $o->dir);
            }
        }

        if(isset($limit) && isset($limit->from)) {
            $query->offset($limit->from);
            if(isset($limit->amount)) {
                $query->limit($limit->amount);
            }
        }

        if(isset($columns)) {
            foreach($columns as $c) {
                if(isset($c->func) && $this->isValidFunction($c->func)) {
                    $select =  $this->getAsRaw($c->func, $c->col, $c->func_values, $c->as);
                } else {
                    $select = '';
                    if(isset($c->as)) {
                        $select = " AS $c->as";
                    }
                    $select = $c->col.$select;
                }
                $query->addSelect($select);
            }
        }

        return $query;
    }

    private function applyFilter($query, $filter, $groups) {
        if(!$this->isValidCompare($filter->comp)) {
            // TODO error?
            return;
        }
        $col = $filter->col;
        $comp = $filter->comp;
        $compValue = $filter->comp_value;
        $and = $filter->and;
        $usesFunc = isset($filter->func);
        if($usesFunc) {
            $func = $filter->func;
            $funcValues = $filter->func_values;
            if(!$this->isValidFunction($func)) {
                // TODO error?
                return;
            }
        }
        $isPartOfGroupBy = $this->isGroupBy($col, $groups);
        $cols = explode('.', $col);
        $isAgg = $usesFunc && $this->isAggregateFunction($func);
        // has no '.' => object itself
        if(count($cols) == 1) {
            if($usesFunc) {
                $col = $this->getAsRaw($func, $col, $funcValues);
            }
            if($and) {
                if($isAgg || $isPartOfGroupBy) {
                    $query->having($col, $comp, $compValue);
                } else {
                    $query->where($col, $comp, $compValue);
                }
            } else {
                if($isAgg || $isPartOfGroupBy) {
                    $query->orHaving($col, $comp, $compValue);
                } else {
                    $query->orWhere($col, $comp, $compValue);
                }
            }
        } else {
            $tbl = $cols[0];
            $tblCol = $cols[1];
            if($usesFunc) {
                $tblCol = $this->getAsRaw($func, $tblCol, $funcValues);
            }
            if($and) {
                $query->whereHas($tbl, function($q) use($tblCol, $comp, $compValue, $isAgg, $isPartOfGroupBy) {
                    if($isAgg || $isPartOfGroupBy) {
                        $q->having($tblCol, $comp, $compValue);
                    } else {
                        $q->where($tblCol, $comp, $compValue);
                    }
                });
            } else {
                $query->orWhereHas($tbl, function($q) use($tblCol, $comp, $compValue, $isAgg, $isPartOfGroupBy) {
                    if($isAgg || $isPartOfGroupBy) {
                        $q->having($tblCol, $comp, $compValue);
                    } else {
                        $q->where($tblCol, $comp, $compValue);
                    }
                });
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
            case 'between':
            case 'in':
            case 'not like':
            case 'not ilike':
            case 'not between':
            case 'not in':
                return true;
            default:
                return false;
        }
    }

    private function isValidFunction($func) {
        switch($func) {
            case 'pg_distance':
            case 'pg_area':
            case 'count':
            case 'min':
            case 'max':
            case 'avg':
            case 'sum':
                return true;
            default:
                return false;
        }
    }

    private function isAggregateFunction($func) {
        if(!isset($func)) return false;
        switch($func) {
            case 'count':
            case 'min':
            case 'max':
            case 'avg':
            case 'sum':
                return true;
            default:
                return false;
        }
    }

    private function getAsRaw($func, $column, $values, $alias = null) {
        $as = '';
        if(isset($alias)) {
            $as = " AS $alias";
        }
        switch($func) {
            case 'pg_distance':
                $pos = $values[0];
                $point = new Point($pos[0], $pos[1]);
                $wkt = $point->toWKT();
                return DB::raw("ST_Distance($column, ST_GeogFromText('$wkt'), true)$as");
            case 'pg_area':
                // return area as sqm, sqm should be default for SRID 4326
                return DB::raw("ST_Area($column, true)$as");
            case 'count':
                return DB::raw("COUNT($column)$as");
            case 'min':
                return DB::raw("MIN($column)$as");
            case 'max':
                return DB::raw("MAX($column)$as");
            case 'avg':
                return DB::raw("AVG($column)$as");
            case 'sum':
                return DB::raw("SUM($column)$as");
        }
    }

    private function isGroupBy($column, $groups) {
        return in_array($column, $groups);
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
