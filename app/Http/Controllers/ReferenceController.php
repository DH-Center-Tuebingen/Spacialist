<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Attribute;
use App\Context;
use App\Reference;
use Illuminate\Database\Eloquent\ModelNotFoundException;
class ReferenceController extends Controller {
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    // GET

    public function getByContext($id) {
        $user = auth()->user();
        if(!$user->can('view_concept_props')) {
            return response()->json([
                'error' => 'You do not have the permission to view references'
            ], 403);
        }
        $references = Reference::with(['attribute', 'bibliography'])->where('context_id', $id)->get();

        $groupedReferences = [];
        foreach($references as $r) {
            $key = $r->attribute->thesaurus_url;
            if(!isset($groupedReferences[$key])) {
                $groupedReferences[$key] = [];
            }
            unset($r->attribute);
            $groupedReferences[$key][] = $r;
        }

        return response()->json($groupedReferences);
    }

    // POST

    public function addReference(Request $request, $id, $aid) {
        $user = auth()->user();
        if(!$user->can('duplicate_edit_concepts')) {
            return response()->json([
                'error' => 'You do not have the permission to add references'
            ], 403);
        }
        $this->validate($request, Reference::rules);

        try {
            Context::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This context does not exist'
            ], 400);
        }
        try {
            Attribute::findOrFail($aid);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This attribute does not exist'
            ], 400);
        }

        $props = array_merge([
            'context_id' => $id,
            'attribute_id' => $aid
        ], $request->only(array_keys(Reference::rules)));
        $reference = Reference::add($props, $user);
        return response()->json($reference, 201);
    }

    // PATCH

    public function patchReference(Request $request, $id) {
        $user = auth()->user();
        if(!$user->can('duplicate_edit_concepts')) {
            return response()->json([
                'error' => 'You do not have the permission to edit references'
            ], 403);
        }
        $this->validate($request, Reference::patchRules);

        try {
            $reference = Reference::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This reference does not exist'
            ], 400);
        }

        $reference->patch($request->only(array_keys(Reference::patchRules)));
        return response()->json(null, 204);
    }

    // PUT

    // DELETE

    public function delete($id) {
        $user = auth()->user();
        if(!$user->can('duplicate_edit_concepts')) {
            return response()->json([
                'error' => 'You do not have the permission to delete references'
            ], 403);
        }
        try {
            $reference = Reference::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'No ContextAttribute found'
            ], 400);
        }
        $reference->delete();

        return response()->json(null, 204);
    }
}
