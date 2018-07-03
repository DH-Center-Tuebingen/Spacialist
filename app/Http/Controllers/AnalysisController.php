<?php

namespace App\Http\Controllers;

use \DB;
use App\Attribute;
use App\AttributeValue;
use App\Context;
use App\File;
use App\Geodata;
use App\Helpers;
use App\Bibliography;
use Phaza\LaravelPostgis\Geometries\Geometry;
use Phaza\LaravelPostgis\Geometries\Point;
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

    public function export($type = 'csv', Request $request) {
        // TODO validate
        $origin = $request->input('origin');
        $filters = $request->input('filters', []);
        $columns = $request->input('columns', []);
        $orders = $request->input('orders', []);
        $limit = $request->input('limit', []);
        $splits = $request->input('splits', []);
        $simple = Helpers::parseBoolean($request->input('simple', false));
        $distinct = Helpers::parseBoolean($request->input('distinct', false));
        $page = $request->input('page');
        $result = $this->requestToQuery($origin, $filters, $columns, $orders, $limit, $splits, $simple, $distinct, $page);
        switch($type) {
            case 'csv':
            // XLSX and PDF files are created from a temporary CSV file
            case 'xlsx':
            case 'pdf':
                $suffix = 'csv';
                break;
            case 'json':
                $suffix = 'json';
                break;
            default:
                return response()->json([
                    'error' => "The type $type is not supported."
                ]);
        }
        $dt = date('dmYHis');
        $tmpFile = "/tmp/export-$dt.$suffix";
        $handle = fopen($tmpFile, 'w');
        $firstRow = true;
        $exceptions = [];
        if($simple) {
            switch($origin) {
                case 'attribute_values':
                    $exceptions = [
                        'attribute',
                        'context',
                        'context_val',
                        'thesaurus_val'
                    ];
                    break;
                case 'contexts':
                    $exceptions = [
                        'child_contexts',
                        'context_type',
                        'geodata',
                        'root_context',
                        'literatures',
                        'attributes',
                        'files'
                    ];
                case 'files':
                    $exceptions = [
                        'contexts',
                        // 'tags'
                    ];
                    break;
                case 'geodata':
                    $exceptions = [
                        'context'
                    ];
                    break;
                case 'literature':
                    $exceptions = [
                        'contexts'
                    ];
                    break;
            }
        }
        $splitIndex = 0;
        $content;
        switch($type) {
            case 'csv':
            case 'xlsx':
            case 'pdf':
                $i=0;
                foreach($result['page']->items() as $row) {
                    $i++;
                    $curr = [];
                    $header = [];
                    foreach($row->getAttributes() as $k => $a) {
                        // TODO skip ambiguous attributes for now
                        if(in_array($k, $exceptions)) continue;
                        if($firstRow) {
                            $header[] = $k;
                        }
                        $curr[] = $a;
                    }
                    if(isset($result['splits'])) {
                        foreach($result['splits'] as $k => $s) {
                            if($firstRow) {
                                $header[] = $k;
                            }
                            $curr[] = $s['values'][$splitIndex];
                        }
                    }
                    if($firstRow) {
                        fputcsv($handle, $header);
                        $firstRow = false;
                    }
                    fputcsv($handle, $curr);
                    $splitIndex++;
                }
                // get raw parsed content
                $content = file_get_contents($tmpFile);
                break;
            case 'json':
                $content = json_encode($result['page']->items(), JSON_PRETTY_PRINT);
                break;
        }
        if($type == 'xlsx' || $type == 'pdf') {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
            $spreadsheet = $reader->load($tmpFile);
            $outFile = "/tmp/export-$dt.$type";
            if($type == 'xlsx') {
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            } else if($type == 'pdf') {
                $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Tcpdf');
            }
            $writer->save($outFile);
            $content = file_get_contents($outFile);
            unlink($outFile);
        }
        // delete tmp file
        fclose($handle);
        unlink($tmpFile);

        // Set correct mime type
        switch($type) {
            case 'csv':
                $contentType = 'text/csv';
                break;
            case 'json':
                $contentType = 'application/json';
                break;
            case 'pdf':
                $contentType = 'application/pdf';
                break;
            case 'xlsx':
                $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                break;
        }
        return response(base64_encode($content))->header('Content-Type', $contentType);
    }

    // POST
    public function applyFilterQuery(Request $request, $page = 1) {
        // TODO validate
        $origin = $request->input('origin');
        $filters = $request->input('filters', []);
        $columns = $request->input('columns', []);
        $orders = $request->input('orders', []);
        $limit = $request->input('limit', []);
        $splits = $request->input('splits', []);
        $simple = Helpers::parseBoolean($request->input('simple', false));
        $distinct = Helpers::parseBoolean($request->input('distinct', false));
        $result = $this->requestToQuery($origin, $filters, $columns, $orders, $limit, $splits, $simple, $distinct, $page);
        return response()->json($result);
    }

    // PATCH

    // PUT

    // DELETE

    // OTHER FUNCTIONS

    private function requestToQuery($origin, $filters, $columns, $orders, $limit, $splits, $simple, $distinct, $page) {
        $query = $this->filter($origin, $filters, $columns, $orders, $distinct, $simple);

        $pageCount = $this->getPerPage($limit);
        if(!isset($pageCount)) $pageCount = 15;
        $rows = $query->paginate($pageCount);
        $rows->withPath('/analysis/filter');

        $total = 0;

        switch($origin) {
            case 'attribute_values':
                $total = AttributeValue::count();
                foreach($rows->items() as $a) {
                    $a->value = $a->getValue();
                }
                break;
            case 'contexts':
                $total = Context::count();
                foreach($rows->items() as $c) {
                    foreach($c->attributes as $a) {
                        $a->pivot->value = AttributeValue::getValueById($a->pivot->attribute_id, $a->pivot->context_id);
                    }
                }
                break;
            case 'geodata':
                $total = Geodata::count();
                break;
            case 'bibliography':
                $total = Bibliography::count();
                break;
            case 'files':
                $total = File::count();
                foreach($rows->items() as $f) {
                    $f->setFileInfo();
                }
                break;
        }

        if($simple) {
            $splitArray = $this->addRelationSplits($rows->items(), $splits);
        }

        if($origin === 'contexts') {
            foreach($rows->items() as $r) {
                if(isset($r->geodata)) {
                    $r->geodata['wkt'] = $r->geodata->geom->toWKT();
                }
            }
        } else if($origin === 'geodata') {
            foreach($rows->items() as &$r) {
                $r->geowkt = $r->geom->toWKT();
            }
        }

        $result = [
            'page' => $rows,
            'hidden' => $total - $rows->total()
        ];
        if(!$simple) {
            $result['query'] = $this->cleanSql($query->toSql());
        } else {
            $result['splits'] = $splitArray;
        }
        return $result;
    }

    private function getPerPage($limit) {
        if(!empty($limit)) {
            if(isset($limit['amount'])) {
                return $limit['amount'];
            }
        }
    }

    private function addRelationSplits($rows, $splits) {
        if(empty($splits)) return null;

        $splitArray = [];
        foreach($splits as $s) {
            $curr = [];
            foreach($rows as $row) {
                $rel = $row->{$s['relation']};
                $value = null;
                // check if $rel is a collection
                if(is_a($rel, 'Illuminate\Database\Eloquent\Collection')) {
                    // if so, loop over all items
                    foreach($rel->all() as $r) {
                        if($r->{$s['column']} == $s['value']) {
                            $value = null;
                            if(isset($r->pivot->str_val)) {
                                $value = $r->pivot->str_val;
                            } else if(isset($r->pivot->int_val)) {
                                $value = $r->pivot->int_val;
                            } else if(isset($r->pivot->dbl_val)) {
                                $value = $r->pivot->dbl_val;
                            } else if(isset($r->pivot->thesaurus_val)) {
                                $value = $r->pivot->thesaurus_val;
                            } else if(isset($r->pivot->dt_val)) {
                                $value = $r->pivot->dt_val;
                            } else if(isset($r->pivot->geography_val)) {
                                $value = [
                                    'wkt' => Geometry::fromWKB($r->pivot->geography_val)->toWKT(),
                                    'geom' => Geometry::fromWKB($r->pivot->geography_val)
                                ];
                            }
                            $type = $r->datatype;
                        }
                    }
                    // otherwise, should be object
                } else if(is_object($rel)) {
                    if($rel->{$s['column']} == $s['value']) {
                        $value = null;
                        if(isset($rel->pivot->str_val)) {
                            $value = $rel->pivot->str_val;
                        } else if(isset($rel->pivot->int_val)) {
                            $value = $rel->pivot->int_val;
                        } else if(isset($rel->pivot->dbl_val)) {
                            $value = $rel->pivot->dbl_val;
                        } else if(isset($rel->pivot->thesaurus_val)) {
                            $value = $rel->pivot->thesaurus_val;
                        } else if(isset($rel->pivot->dt_val)) {
                            $value = $rel->pivot->dt_val;
                        } else if(isset($r->pivot->geography_val)) {
                            $value = [
                                'wkt' => Geometry::fromWKB($rel->pivot->geography_val)->toWKT(),
                                'geom' => Geometry::fromWKB($rel->pivot->geography_val)
                            ];
                        }
                        $type = $rel->datatype;
                    }
                } else {
                    // should not happen ;)
                }
                $curr[] = $value;
            }
            $relName = $s['name'];
            $keys = array_keys($splitArray);
            if(!empty($keys)) {
                $hits = 0;
                $quotedName = "|". preg_quote($relName) . "( \(\d+\))?|";
                foreach($keys as $key) {
                    if(preg_match($quotedName, $key) === 1) {
                        $hits++;
                    }
                }
                if($hits > 0) {
                    $relName .= " ($hits)";
                }
            }
            $splitArray[$relName] = [
                'values' => $curr,
                'type'   => $type
            ];
        }

        return $splitArray;
    }

    private function filter($origin, $filters, $columns, $orders, $distinct, $relations = false) {
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
            case 'bibliography':
                if($relations) {
                    $query = Bibliography::with([
                        // 'contexts'
                    ]);
                } else {
                    $query = Bibliography::leftJoin('sources', 'sources.literature_id', '=', 'literature.id')
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
            foreach($filters as $filterGroup) {
                $query->where(function($subQuery) use ($filterGroup, $groups, $hasGroupBy) {
                    foreach($filterGroup as $fArray) {
                        $f = (object) $fArray;
                        $f->and = false; // all filters in a group are OR
                        $applied = $this->applyFilter($subQuery, $f);
                        // check if it was a valid filter and a agg function
                        if($applied && isset($f->func) && $this->isAggregateFunction($f->func)) {
                            $hasGroupBy = true;
                        } else {
                            $groups[$f->column] = 1;
                        }
                    }
                });
            }
        }

        if(!empty($orders)) {
            foreach($orders as $o) {
                $query->orderBy($o->col, $o->dir);
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

    private function applyFilter($query, $filter) {
        if(!$this->isValidCompare($filter->comp)) {
            // TODO error?
            return false;
        }
        $col = $filter->column;
        $comp = strtoupper($filter->comp);
        $compValue = null;
        if(isset($filter->comp_value)) {
            $compValue = $filter->comp_value;
        }
        if(isset($filter->relation)) {
            $relation = (object) $filter->relation;
            $isRelationFilter = isset($relation->name);
        } else {
            $isRelationFilter = false;
        }
        $and = $filter->and;
        $usesFunc = isset($filter->func);
        if($usesFunc) {
            $func = $filter->func;
            $funcValues = null;
            if(isset($filter->func_values)) {
                $funcValues = json_decode($filter->func_values);
            }
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
                            if($comp != 'IS NULL' && $comp != 'IS NOT NULL') {
                                $this->applyQueryPart($q, $col, $comp, $compValue, true);
                            }
                        });
                    } else {
                        $query->orWhereHas($relation->name, function($q) use($col, $comp, $compValue, $relation) {
                            if(isset($relation->id)) {
                                $q->where($relation->name . '.id', '=', $relation->id);
                            }
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

    private function applyQueryPart($query, $column, $cmp, $values, $and) {
        switch($cmp) {
            case 'BETWEEN':
                if($and) $query->whereBetween($column, $values);
                else $query->orWhereBetween($column, $values);
                break;
            case 'IN':
                if($and) $query->whereIn($column, $values);
                else $query->orWhereIn($column, $values);
                break;
            case 'IS NULL':
                if($and) $query->whereNull($column);
                else $query->orWhereNull($column);
                break;
            case 'NOT BETWEEN':
                if($and) $query->whereNotBetween($column, $values);
                else $query->whereNotBetween($column, $values);
                break;
            case 'NOT IN':
                if($and) $query->whereNotIn($column, $values);
                else $query->orWhereNotIn($column, $values);
                break;
            case 'IS NOT NULL':
                if($and) $query->whereNotNull($column);
                else $query->orWhereNotNull($column);
                break;
            default:
                if($and) $query->where($column, $cmp, $values);
                else $query->orWhere($column, $cmp, $values);
                break;
        }
    }

    private function isValidCompare($cmp) {
        $cmp = strtoupper($cmp);
        switch($cmp) {
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
        $func = strtoupper($func);
        if($this->isAggregateFunction($func)) return true;
        switch($func) {
            case 'PG_DISTANCE':
            case 'PG_AREA':
            case 'GEOMETRYTYPE':
                return true;
            default:
                return false;
        }
    }

    private function isAggregateFunction($func) {
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
            case 'GEOMETRYTYPE':
                return DB::raw("GeometryType($column)$as");
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
