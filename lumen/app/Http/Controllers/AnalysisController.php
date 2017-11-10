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
        $origin = $request->input('origin');
        $filters = json_decode($request->input('filters', '[]'));
        $columns = json_decode($request->input('columns', '[]'));
        $query = $this->filter($origin, $filters, $columns);
        $rows = $query->get();
        return response()->json($rows);
    }

    // PATCH

    // PUT

    // DELETE

    // OTHER FUNCTIONS

    private function filter($origin, $filters, $columns) {
        switch($origin) {
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
                $query = Files::with([
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
                $this->applyFilter($query, $f);
            }
        }

        return $query;
    }

    private function applyFilter($query, $filter) {
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
        $cols = explode('.', $col);
        // has no '.' => object itself
        if(count($cols) == 1) {
            if($usesFunc) {
                $col = $this->getAsRaw($func, $col, $funcValues);
            }
            if($and) {
                $query->where($col, $comp, $compValue);
            } else {
                $query->orWhere($col, $comp, $compValue);
            }
        } else {
            $tbl = $cols[0];
            $tblCol = $cols[1];
            if($usesFunc) {
                $tblCol = $this->getAsRaw($func, $tblCol, $funcValues);
            }
            if($and) {
                $query->whereHas($tbl, function($q) use($tblCol, $comp, $compValue) {
                    $q->where($tblCol, $comp, $compValue);
                });
            } else {
                $query->orWhereHas($tbl, function($q) use($tblCol, $comp, $compValue) {
                    $q->where($tblCol, $comp, $compValue);
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
                $pos = $values[0];
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
