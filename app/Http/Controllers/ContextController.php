<?php

namespace App\Http\Controllers;

use App\Attribute;
use App\AttributeValue;
use App\Context;
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
            $data[$a->attribute_id] = $a;
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

        $context = new Context();
        $rank;
        if($request->has('root_context_id')) {
            $rank = Context::where('root_context_id', '=', $request->get('root_context_id'))->max('rank') + 1;
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
