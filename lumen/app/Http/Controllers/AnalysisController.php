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
use App\Helpers;
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

    private function filter($origin, $filters, $columns, $orders, $groups, $limit, $relations = false) {
        $hasColumnSelection = !empty($columns);

        switch($origin) {
            case 'attribute_values':
                if($relations) {
                    $query = AttributeValue::with([
                        'context_val',
                        'thesaurus_val'
                    ]);
                } else {
                    $query = AttributeValue::leftJoin('contexts', 'contexts.id', '=', 'context_val');
                    if(!$hasColumnSelection) {
                        $tables = ['attribute_values', 'contexts'];

                        $columnNames = [];
                        foreach($tables as $table) {
                            $columnNames[$table] = Helpers::getColumnNames($table);
                        }

                        $this->renameColumns($query, $tables, $columnNames);
                    }
                }
                break;
            case 'contexts':
                if($relations) {
                    $query = Context::with([
                        'child_contexts',
                        'context_type',
                        'geodata',
                        'root_context',
                        'literatures',
                        'attributes',
                        'files'
                    ]);
                } else {
                    $query = Context::leftJoin('contexts as child', 'child.root_context_id', '=', 'contexts.id')
                                    ->leftJoin('contexts as root', 'root.id', '=', 'contexts.root_context_id')
                                    ->leftJoin('context_types', 'context_types.id', '=', 'contexts.context_type_id')
                                    ->leftJoin('geodata', 'geodata.id', '=', 'contexts.geodata_id')
                                    ->leftJoin('attribute_values', 'attribute_values.context_id', '=', 'contexts.id')
                                    ->leftJoin('attributes', 'attributes.id', '=', 'attribute_id')
                                    ->leftJoin('context_photos as cp', 'cp.context_id', '=', 'contexts.id')
                                    ->leftJoin('photos', 'photos.id', '=', 'photo_id');
                    if(!$hasColumnSelection) {
                        $tables = ['contexts', 'child', 'root', 'context_types', 'geodata', 'attribute_values', 'attributes', 'photos'];
                        $columnNames = [];
                        foreach($tables as $table) {
                            if($table === 'child' || $table === 'root') {
                                $columnNames[$table] = Helpers::getColumnNames('contexts');
                            } else {
                                $columnNames[$table] = Helpers::getColumnNames($table);
                            }
                        }

                        $this->renameColumns($query, $tables, $columnNames);
                    }
                }
                break;
            case 'files': //TODO: rename columns
                if($relations) {
                    $query = File::with([
                        'contexts',
                        'tags'
                    ]);
                } else {
                    $query = File::leftJoin('context_photos as cp', 'cp.photo_id', '=', 'id')
                                    ->leftJoin('contexts', 'contexts.id', '=', 'context_id')
                                    ->leftJoin('photo_tags as pt', 'pt.photo_id', '=', 'photos.id')
                                    ->leftJoin('th_concept', 'th_concept.concept_url', '=', 'pt.concept_url');
                                    if(!$hasColumnSelection) {
                                        $query->select('contexts.*')->addSelect('th_concept.*')->addSelect('photos.*');
                                    }
                }
                break;
            case 'geodata':
                if($relations) {
                    $query = Geodata::with([
                        'context'
                    ]);
                    break;
                } else {
                    $query = Geodata::leftJoin('contexts', 'contexts.geodata_id', '=', 'id');
                }
            case 'literature': //TODO: $relation
                $query = Literature::with([
                    'contexts'
                ]);
                break;
        }
        if(!empty($filters)) {
            foreach($filters as $f) {
                $this->applyFilter($query, $f, $groups);
            }
        }

        if(!empty($groups)) {
            foreach($groups as $g) {
                $query->groupBy($g);
            }
        }
        if(!empty($orders)) {
            foreach($orders as $o) {
                $query->orderBy($o->col, $o->dir);
            }
        }

        if(!empty($limit)) {
            if(isset($limit->from)) {
                $query->offset($limit->from);
            }
            if(isset($limit->amount)) {
                $query->limit($limit->amount);
            }
        }

        if($hasColumnSelection) {
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

    // renames columns from $column to $table.$column to avoid name ambiguities
    private function renameColumns($query, $tables, $columnNames) {
        if(empty($tables)) return;

        $query->select($tables[0].".id as ".$tables[0].".id");
        foreach($tables as $table) {
            foreach($columnNames[$table] as $c) {
                $query->addSelect("$table.$c as $table.$c");
            }
        }
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
                    switch($comp) {
                        case 'between':
                            $query->whereBetween($col, $compValue);
                            break;
                        case 'in':
                            $query->whereIn($col, $compValue);
                            break;
                        case 'is null':
                            $query->whereNull($col);
                            break;
                        case 'not between':
                            $query->whereNotBetween($col, $compValue);
                            break;
                        case 'not in':
                            $query->whereNotIn($col, $compValue);
                            break;
                        case 'is not null':
                            $query->whereNotNull($col);
                            break;
                        default:
                            $query->where($col, $comp, $compValue);
                            break;
                    }
                }
            } else {
                if($isAgg || $isPartOfGroupBy) {
                    $query->orHaving($col, $comp, $compValue);
                } else {
                    switch($comp) {
                        case 'between':
                            $query->orWhereBetween($col, $compValue);
                            break;
                        case 'in':
                            $query->orWhereIn($col, $compValue);
                            break;
                        case 'is null':
                            $query->orWhereNull($col);
                            break;
                        case 'not between':
                            $query->orWhereNotBetween($col, $compValue);
                            break;
                        case 'not in':
                            $query->orWhereNotIn($col, $compValue);
                            break;
                        case 'is not null':
                            $query->orWhereNotNull($col);
                            break;
                        default:
                            $query->orWhere($col, $comp, $compValue);
                            break;
                    }
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
                        switch($comp) {
                            case 'between':
                                $query->whereBetween($tblCol, $compValue);
                                break;
                            case 'in':
                                $query->whereIn($tblCol, $compValue);
                                break;
                            case 'is null':
                                $query->whereNull($tblCol);
                                break;
                            case 'not between':
                                $query->whereNotBetween($tblCol, $compValue);
                                break;
                            case 'not in':
                                $query->whereNotIn($tblCol, $compValue);
                                break;
                            case 'is not null':
                                $query->whereNotNull($tblCol);
                                break;
                            default:
                                $query->where($tblCol, $comp, $compValue);
                                break;
                        }
                    }
                });
            } else {
                $query->orWhereHas($tbl, function($q) use($tblCol, $comp, $compValue, $isAgg, $isPartOfGroupBy) {
                    if($isAgg || $isPartOfGroupBy) {
                        $q->having($tblCol, $comp, $compValue);
                    } else {
                        switch($comp) {
                            case 'between':
                                $query->whereBetween($tblCol, $compValue);
                                break;
                            case 'in':
                                $query->whereIn($tblCol, $compValue);
                                break;
                            case 'is null':
                                $query->whereNull($tblCol);
                                break;
                            case 'not between':
                                $query->whereNotBetween($tblCol, $compValue);
                                break;
                            case 'not in':
                                $query->whereNotIn($tblCol, $compValue);
                                break;
                            case 'is not null':
                                $query->whereNotNull($tblCol);
                                break;
                            default:
                                $query->where($tblCol, $comp, $compValue);
                                break;
                        }
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
            case 'is null':
            case 'not like':
            case 'not ilike':
            case 'not between':
            case 'not in':
            case 'is not null':
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
