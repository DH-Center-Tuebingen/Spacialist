<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Attribute;
use App\Entity;
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

    public function getByEntity($id) {
        $user = auth()->user();
        if(!$user->can('entity_read') || !$user->can('entity_data_read')) {
            return response()->json([
                'error' => __('You do not have the permission to view references')
            ], 403);
        }

        try {
            Entity::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist')
            ], 400);
        }

        $references = Reference::with(['attribute', 'bibliography'])->where('entity_id', $id)->get();

        $groupedReferences = [];
        foreach($references as $r) {
            if(isset($r->attribute)) {
                $key = $r->attribute->thesaurus_url;
            } else {
                $key = 'on_entity';
            }
            if(!isset($groupedReferences[$key])) {
                $groupedReferences[$key] = [];
            }
            unset($r->attribute);
            $groupedReferences[$key][] = $r;
        }

        return response()->json($groupedReferences);
    }

    // POST

    public function addReference(Request $request, int $id, ?int $aid = null) {
        $user = auth()->user();
        if(!$user->can('entity_data_create')) {
            return response()->json([
                'error' => __('You do not have the permission to add references')
            ], 403);
        }
        $this->validate($request, Reference::rules);

        try {
            Entity::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist')
            ], 400);
        }

        $data = [
            'entity_id' => $id,
        ];
        if(isset($aid)) {
            try {
                Attribute::findOrFail($aid);
                $data['attribute_id'] = $aid;
            } catch(ModelNotFoundException $e) {
                return response()->json([
                    'error' => __('This attribute does not exist')
                ], 400);
            }
        }

        $props = array_merge(
            $data,
            $request->only(array_keys(Reference::rules))
        );
        $reference = Reference::add($props, $user);
        return response()->json($reference, 201);
    }

    // PATCH

    public function patchReference(Request $request, $id) {
        $user = auth()->user();
        if(!$user->can('entity_data_write')) {
            return response()->json([
                'error' => __('You do not have the permission to edit references')
            ], 403);
        }
        $this->validate($request, Reference::patchRules);

        try {
            $reference = Reference::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This reference does not exist')
            ], 400);
        }

        $reference->patch($request->only(array_keys(Reference::patchRules)));
        return response()->json($reference);
    }

    // PUT

    // DELETE

    public function delete($id) {
        $user = auth()->user();
        if(!$user->can('entity_data_delete')) {
            return response()->json([
                'error' => __('You do not have the permission to delete references')
            ], 403);
        }
        try {
            $reference = Reference::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This reference does not exist')
            ], 400);
        }
        $reference->delete();

        return response()->json(null, 204);
    }
}
