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
        $limit = json_decode($request->input('limit', '{}'));
        $query = $this->filter([$ctid], $filters, $columns, $orders, $limit);
        return response()->json([
            'rows' => $query->get(),
            'query' => $this->cleanSql($query->toSql())
        ]);
    }

    public function filterContexts(Request $request) {
        $origin = $request->input('origin');
        $filters = json_decode($request->input('filters', '[]'));
        $columns = json_decode($request->input('columns', '[]'));
        $orders = json_decode($request->input('orders', '[]'));
        $limit = json_decode($request->input('limit', '{}'));
        $simple = Helpers::parseBoolean($request->input('simple', false));
        $distinct = Helpers::parseBoolean($request->input('distinct', false));
        $query = $this->filter($origin, $filters, $columns, $orders, $limit, $distinct, $simple);
        $result = [
            'rows' => $query->get()
        ];
        if(!$simple) {
            $result['query'] = $this->cleanSql($query->toSql());
        }
        return response()->json($result);
    }

    // PATCH

    // PUT

    // DELETE

    // OTHER FUNCTIONS

    private function filter($origin, $filters, $columns, $orders, $limit, $distinct, $relations = false) {
        $hasColumnSelection = !empty($columns);

        switch($origin) {
            case 'attribute_values':
                if($relations) {
                    $query = AttributeValue::with([
                        'attribute',
                        'context',
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
            case 'files':
                if($relations) {
                    $query = File::with([
                        'contexts',
                        // 'tags'
                    ]);
                } else {
                    $query = File::leftJoin('context_photos as cp', 'cp.photo_id', '=', 'id')
                                    ->leftJoin('contexts', 'contexts.id', '=', 'context_id')
                                    ->leftJoin('photo_tags as pt', 'pt.photo_id', '=', 'photos.id')
                                    ->leftJoin('th_concept', 'th_concept.concept_url', '=', 'pt.concept_url');
                    if(!$hasColumnSelection) {
                        $tables = ['photos', 'contexts', 'th_concept'];
                        $columnNames = [];
                        foreach($tables as $table) {
                            $columnNames[$table] = Helpers::getColumnNames($table);
                        }

                        $this->renameColumns($query, $tables, $columnNames);
                    }
                }
                break;
            case 'geodata':
                if($relations) {
                    $query = Geodata::with([
                        'context'
                    ]);
                } else {
                    $query = Geodata::leftJoin('contexts', 'contexts.geodata_id', '=', 'geodata.id');
                    if(!$hasColumnSelection) {
                        $tables = ['geodata', 'contexts'];
                        $columnNames = [];
                        foreach($tables as $table) {
                            $columnNames[$table] = Helpers::getColumnNames($table);
                        }

                        $this->renameColumns($query, $tables, $columnNames);
                    }
                }
                break;
            case 'literature':
                if($relations) {
                    $query = Literature::with([
                        'contexts'
                    ]);
                } else {
                    $query = Literature::leftJoin('sources', 'sources.literature_id', '=', 'literature.id')
                        ->leftJoin('contexts', 'sources.context_id', '=', 'contexts.id')
                        ->leftJoin('attributes', 'sources.attribute_id', '=', 'attributes.id');
                    if(!$hasColumnSelection) {
                        $tables = ['literature', 'attributes', 'contexts', 'sources'];
                        $columnNames = [];
                        foreach($tables as $table) {
                            $columnNames[$table] = Helpers::getColumnNames($table);
                        }

                        $this->renameColumns($query, $tables, $columnNames);
                    }
                }
                break;
        }
        $groups = [];
        $hasGroupBy = false;
        if(!empty($filters)) {
            foreach($filters as $f) {
                $applied = $this->applyFilter($query, $f, $groups);
                // check if it was a valid filter and a agg function
                if($applied && isset($f->func) && $this->isAggregateFunction($f->func)) {
                    $hasGroupBy = true;
                } else {
                    $groups[$f->col] = 1;
                }
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

        if($distinct) {
            $query->distinct();
        }

        if($hasColumnSelection) {
            // check if there is at least one agg function
            foreach($columns as $c) {
                if(isset($c->func) && $this->isValidFunction($c->func)) {
                    if($this->isAggregateFunction($c->func)) {
                        $hasGroupBy = true;
                    } else {
                        $groups[$c->col] = 1;
                    }
                    $select =  $this->getAsRaw($c->func, $c->col, $c->func_values, $c->as);
                } else {
                    $groups[$c->col] = 1;
                    $select = '';
                    if(isset($c->as)) {
                        $select = " AS $c->as";
                    }
                    $select = $c->col.$select;
                }
                $query->addSelect($select);
            }
            if($hasGroupBy && !empty($groups)) {
                foreach($groups as $col => $set) {
                    if($set === 1) {
                        $query->groupBy($col);
                    }
                }
            }
        }

        return $query;
    }

    // renames columns from $column to $table.$column to avoid name ambiguities
    private function renameColumns($query, $tables, $columnNames) {
        if(empty($tables)) return;

        $query->select($tables[0].".id AS ".$tables[0].".id");
        foreach($tables as $table) {
            foreach($columnNames[$table] as $c) {
                $query->addSelect("$table.$c AS $table.$c");
            }
        }
    }

    private function applyFilter($query, $filter, $groups) {
        if(!$this->isValidCompare($filter->comp)) {
            // TODO error?
            return false;
        }
        $col = $filter->col;
        $comp = strtoupper($filter->comp);
        $compValue = null;
        if(isset($filter->comp_value)) {
            $compValue = $filter->comp_value;
        }
        if(isset($filter->relation) && isset($filter->relation->name)) {
            $isRelationFilter = true;
            $relation = $filter->relation;
        } else {
            $isRelationFilter = false;
        }
        $and = $filter->and;
        $usesFunc = isset($filter->func);
        if($usesFunc) {
            $func = $filter->func;
            $funcValues = $filter->func_values;
            if(!$this->isValidFunction($func)) {
                // TODO error?
                return false;
            }
        }
        $isAgg = $usesFunc && $this->isAggregateFunction($func);
        if($usesFunc) {
            $col = $this->getAsRaw($func, $col, $funcValues);
        }
        if($isRelationFilter) {
            if($isAgg) {
                if($and) {
                    $query->whereHas($relation->name, function($q) use($col, $comp, $compValue, $relation) {
                        $q->where('id', '=', $relation->id);
                        $q->having($col, $comp, $compValue);
                    });
                } else {
                    $query->orWhereHas($relation->name, function($q) use($col, $comp, $compValue, $relation) {
                        $q->where('id', '=', $relation->id);
                        $q->having($col, $comp, $compValue);
                    });
                }
            } else {
                if(isset($relation->comp)) {
                    if($relation->comp == 'IS NULL') {
                        if($and) $query->doesntHave($relation->name);
                        else $query->orDoesntHave($relation->name);
                    } else if($relation->comp == 'IS NOT NULL') {
                        if($and) $query->has($relation->name);
                        else $query->orHas($relation->name);
                    } else {
                        if($and) $query->has($relation->name, $relation->comp, $relation->value);
                        else $query->orHas($relation->name, $relation->comp, $relation->value);
                    }
                } else {
                    if($and) {
                        $query->whereHas($relation->name, function($q) use($col, $comp, $compValue, $relation) {
                            if(isset($relation->id)) {
                                $q->where($relation->name . '.id', '=', $relation->id);
                            }
                            // TODO is null and is not null are only reasonable
                            // for relation itself?
                            if($comp != 'IS NULL' && $comp != 'IS NOT NULL') {
                                $this->applyQueryPart($q, $col, $comp, $compValue, true);
                            }
                        });
                    } else {
                        $query->orWhereHas($relation->name, function($q) use($col, $comp, $compValue, $relation) {
                            if(isset($relation->id)) {
                                $q->where($relation->name . '.id', '=', $relation->id);
                            }
                            // TODO is null and is not null are only reasonable
                            // for relation itself?
                            if($comp != 'IS NULL' && $comp != 'IS NOT NULL') {
                                $this->applyQueryPart($q, $col, $comp, $compValue, true);
                            }
                        });
                    }
                }
            }
        } else {
            if($isAgg) {
                if($and) $query->having($col, $comp, $compValue);
                else $query->orHaving($col, $comp, $compValue);
            } else {
                $this->applyQueryPart($query, $col, $comp, $compValue, $and);
            }
        }
        return true;
    }

    private function applyQueryPart($query, $col, $comp, $compValue, $and) {
        switch($comp) {
            case 'BETWEEN':
                if($and) $query->whereBetween($col, $compValue);
                else $query->orWhereBetween($col, $compValue);
                break;
            case 'IN':
                if($and) $query->whereIn($col, $compValue);
                else $query->orWhereIn($col, $compValue);
                break;
            case 'IS NULL':
                if($and) $query->whereNull($col);
                else $query->orWhereNull($col);
                break;
            case 'NOT BETWEEN':
                if($and) $query->whereNotBetween($col, $compValue);
                else $query->whereNotBetween($col, $compValue);
                break;
            case 'NOT IN':
                if($and) $query->whereNotIn($col, $compValue);
                else $query->orWhereNotIn($col, $compValue);
                break;
            case 'IS NOT NULL':
                if($and) $query->whereNotNull($col);
                else $query->orWhereNotNull($col);
                break;
            default:
                if($and) $query->where($col, $comp, $compValue);
                else $query->orWhere($col, $comp, $compValue);
                break;
        }
    }

    private function isValidCompare($comp) {
        $compU = strtoupper($comp);
        switch($comp) {
            case '=':
            case '!=':
            case '>':
            case '>=':
            case '<':
            case '<=':
            case 'ILIKE':
            case 'NOT ILIKE':
            case 'BETWEEN':
            case 'NOT BETWEEN':
            case 'IS NULL':
            case 'IS NOT NULL':
            case 'IN':
            case 'NOT IN':
                return true;
            default:
                return false;
        }
    }

    private function isValidFunction($func) {
        if(!isset($func)) return false;
        $func = strtoupper($func);
        if($this->isAggregateFunction($func)) return true;
        switch($func) {
            case 'PG_DISTANCE':
            case 'PG_AREA':
                return true;
            default:
                return false;
        }
    }

    private function isAggregateFunction($func) {
        if(!isset($func)) return false;
        $func = strtoupper($func);
        switch($func) {
            case 'COUNT':
            case 'MIN':
            case 'MAX':
            case 'AVG':
            case 'SUM':
                return true;
            default:
                return false;
        }
    }

    private function getAsRaw($func, $column, $values, $alias = null) {
        $as = '';
        if(isset($alias)) {
            $as = " AS \"$alias\"";
        }
        $func = strtoupper($func);
        switch($func) {
            case 'PG_DISTANCE':
                $pos = $values[0];
                $point = new Point($pos[0], $pos[1]);
                $wkt = $point->toWKT();
                return DB::raw("ST_Distance($column, ST_GeogFromText('$wkt'), true)$as");
            case 'PG_AREA':
                // return area as sqm, sqm should be default for SRID 4326
                return DB::raw("ST_Area($column, true)$as");
            case 'COUNT':
                return DB::raw("COUNT($column)$as");
            case 'MIN':
                return DB::raw("MIN($column)$as");
            case 'MAX':
                return DB::raw("MAX($column)$as");
            case 'AVG':
                return DB::raw("AVG($column)$as");
            case 'SUM':
                return DB::raw("SUM($column)$as");
        }
    }

    private function cleanSql($queryString) {
        return str_replace('"', '', $queryString);
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
