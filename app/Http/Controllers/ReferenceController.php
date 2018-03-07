<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

    // PATCH

    // PUT

    // DELETE

    public function delete($id) {
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
