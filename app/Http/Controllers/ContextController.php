<?php

namespace App\Http\Controllers;

use App\Attribute;
use App\AttributeValue;
use App\Context;
use App\ContextAttribute;
use App\ContextType;
use App\ContextTypeRelation;
use App\ThConcept;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function getData($id) {
        try {
            $context = Context::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This context does not exist'
            ], 400);
        }

        $attributes = AttributeValue::where('context_id', $id)->get();
        $data = [];
        foreach($attributes as $a) {
            $datatype = Attribute::find($a->attribute_id)->datatype;
            switch($datatype) {
                case 'string-sc':
                    $a->thesaurus_val = ThConcept::where('concept_url', $a->thesaurus_val)->first();
                    break;
                default:
                    break;
            }
            $value = $a->getValue();
            $a->value = $value;
            $data[$a->attribute_id] = $a;
        }

        $sqls = ContextAttribute::join('attributes', 'attributes.id', '=', 'attribute_id')
            ->where('context_type_id', $context->context_type_id)
            ->where('datatype', 'sql')
            ->get();
        foreach($sqls as $sql) {
            // if entity_id is referenced several times
            // add an incrementing counter, so the
            // references are unique (required by PDO)
            $cnt = substr_count($sql->text, ':entity_id');
            if($cnt > 1) {
                $safes = [];
                for($i=0; $i<$cnt; $i++) {
                    $safes[':entity_id_'.$i] = $id;
                }
                $i = 0;
                $text = preg_replace_callback('/:entity_id/', function($matches) use (&$i) {
                    return $matches[0].'_'.$i++;
                }, $sql->text);
            } else {
                $text = $sql->text;
                $safes = [
                    ':entity_id' => $id
                ];
            }
            $raw = \DB::raw($text);
            $sqlValue = \DB::select($text, $safes);
            // Check if only one result exists
            if(count($sqlValue) === 1) {
                // Get all column indices (keys) using the first row
                $valueKeys = array_keys(get_object_vars($sqlValue[0]));
                // Check if also only one key/column exists
                if(count($valueKeys) === 1) {
                    // If only one row and one column exist,
                    // return plain value instead of array
                    $firstKey = $valueKeys[0];
                    $sqlValue = $sqlValue[0]->{$firstKey};
                }
            }
            $data[$sql->attribute_id] = [
                'value' => $sqlValue
            ];
        }

        return response()->json($data);
    }

    public function getChildren($id) {
        try {
            $context = Context::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This context does not exist'
            ], 400);
        }
        $children = Context::where('root_context_id', $id)->get();
        return response()->json($children);
    }

    public function getEntitiesByParent($id) {
        return Context::getEntitiesByParent($id);
    }

    // POST

    public function addEntity(Request $request) {
        $this->validate($request, Context::rules);

        $isChild = $request->has('root_context_id');
        $rcid = $request->get('root_context_id');

        if($isChild) {
            $parentCtid = Context::find($rcid)->context_type_id;
            $relation = ContextTypeRelation::where('parent_id', $parentCtid)
                ->where('child_id', $request->get('context_type_id'))->get();
            if(!isset($relation)) {
                return response()->json([
                    'error' => 'This type is not an allowed sub-type.'
                ], 400);
            }
        } else {
            if(!ContextType::find($request->get('context_type_id'))->is_root) {
                return response()->json([
                    'error' => 'This type is not an allowed root-type.'
                ], 400);
            }
        }

        $context = new Context();
        $rank;
        if($isChild) {
            $rank = Context::where('root_context_id', '=', $rcid)->max('rank') + 1;
        } else {
            $rank = Context::whereNull('root_context_id')->max('rank') + 1;
        }
        $context->rank = $rank;

        foreach($request->only(array_keys(Context::rules)) as $key => $value) {
            $context->{$key} = $value;
        }
        $context->lasteditor = 'Admin'; // TODO
        $context->save();

        return response()->json($context, 201);
    }

    // PATCH

    public function patchAttributes($id, Request $request) {
        foreach($request->request as $pid => $patch) {
            $op = $patch['op'];
            $aid = $patch['params']['aid'];
            $attr = Attribute::find($aid);
            switch($op) {
                case 'remove':
                    $attrval = AttributeValue::where([
                        ['context_id', '=', $id],
                        ['attribute_id', '=', $aid]
                    ])->first();
                    $attrval->delete();
                    return response()->json(null, 204);
                case 'add':
                    $value = $patch['value'];
                    $attrval = new AttributeValue();
                    $attrval->context_id = $id;
                    $attrval->attribute_id = $aid;
                    break;
                case 'replace':
                    $value = $patch['value'];
                    $attrval = AttributeValue::where([
                        ['context_id', '=', $id],
                        ['attribute_id', '=', $aid]
                    ])->first();
                    break;
                default:
                    return response()->json([
                        'error' => 'Unknown operation'
                    ], 400);
            }
            switch($attr->datatype) {
                # for primitive types: just save them to the db
                case 'stringf':
                case 'string':
                    $attrval->str_val = $value;
                    break;
                case 'double':
                    $attrval->dbl_val = $value;
                    break;
                case 'boolean':
                case 'percentage':
                case 'integer':
                    $attrval->int_val = $value;
                    break;
                case 'date':
                    $attrval->dt_val = $value;
                    break;
                case 'string-sc':
                    $thesaurus_url = $value['concept_url'];
                    $attrval->thesaurus_val = $thesaurus_url;
                    break;
                case 'string-mc':
                    $thesaurus_urls = [];
                    foreach($value as $val) {
                        $thesaurus_urls[] = [
                            "concept_url" => $val['concept_url'],
                            "id" => $val['id']
                        ];
                    }
                    $attrval->json_val = json_encode($thesaurus_urls);
                    break;
                case 'epoch':
                case 'dimension':
                case 'list':
                case 'table':
                    $attrval->json_val = json_encode($value);
                    break;

                    // 'datatype' => 'geography', TODO
                    // 'datatype' => 'context', TODO

            }
            $attrval->lasteditor = 'Admin'; // TODO
            $attrval->save();
        }
    }

    public function patchAttribute($id, $aid, Request $request) {
        $this->validate($request, AttributeValue::patchRules);

        try {
            $context = Context::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This context does not exist'
            ], 400);
        }
        try {
            $attribute = Attribute::findOrFail($aid);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This attribute does not exist'
            ], 400);
        }

        $attrs = AttributeValue::where('context_id', $id)
            ->where('attribute_id', $aid)
            ->get();
        $values = $request->only(array_keys(AttributeValue::patchRules));
        foreach($attrs as $a) {
            $a->patch($values);
        }

        return response()->json(null, 204);
    }

    // DELETE

    public function deleteContext($id) {
        try {
            $context = Context::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This context does not exist'
            ], 400);
        }
        $context->delete();

        return response()->json(null, 204);
    }
}
