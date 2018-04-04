<?php

namespace App\Http\Controllers;

use App\Attribute;
use App\AttributeValue;
use App\Context;
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
            Context::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This context does not exist'
            ], 400);
        }

        $attributes = AttributeValue::where('context_id', $id)->get();
        $data = [];
        $values = [];
        foreach($attributes as $a) {
            $datatype = Attribute::find($a->attribute_id)->datatype;
            switch($datatype) {
                case 'string-sc':
                case 'string-mc':
                    $a->thesaurus_val = ThConcept::where('concept_url', $a->thesaurus_val)->first();
                    break;
                default:
                    break;
            }
            $value = $a->str_val ??
                $a->int_val ??
                $a->dbl_val ??
                $a->context_val ??
                $a->thesaurus_val ??
                json_decode($a->json_val) ??
                $a->geography_val ??
                $a->dt_val;

            switch($datatype) {
                case 'string-mc':
                case 'list':
                case 'table':
                    if(!isset($data[$a->attribute_id])) {
                        $values[$a->attribute_id] = [$value];
                    } else {
                        $values[$a->attribute_id][] = $value;
                    }
                    break;
                default:
                    break;
            }
            $a->value = $value;
            $data[$a->attribute_id] = $a;
        }
        foreach($values as $k => $v) {
            $data[$k]->value = $v;
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
        //TODO
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
