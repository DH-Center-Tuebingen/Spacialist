<?php

namespace App\Http\Controllers;

use App\Context;
use App\ContextType;
// use App\AvailableLayer;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EditorController extends Controller {
    // GET

    public function getOccurrenceCount($id) {
        $cnt = Context::where('context_type_id', $id)->count();
        return response()->json($cnt);
    }

    // POST

    public function addContextType(Request $request) {
        $this->validate($request, [
            'concept_url' => 'required|url|exists:th_concept',
            // 'geomtype' => 'required|geom_type'
        ]);

        $curl = $request->get('concept_url');
        // $geomtype = $request->get('geomtype');
        $cType = new ContextType();
        $cType->thesaurus_url = $curl;
        $cType->type = 0;
        $cType->save();

        // $layer = new AvailableLayer();
        // $layer->name = '';
        // $layer->url = '';
        // $layer->type = $geomtype;
        // $layer->opacity = 1;
        // $layer->visible = true;
        // $layer->is_overlay = true;
        // $layer->position = AvailableLayer::where('is_overlay', '=', true)->max('position') + 1;
        // $layer->context_type_id = $cType->id;
        // $layer->color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
        // $layer->save();

        return response()->json($cType);
    }

    // DELETE

    public function deleteContextType($id) {
        ContextType::find($id)->delete();
    }
}
