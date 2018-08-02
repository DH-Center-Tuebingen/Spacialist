<?php

namespace App\Http\Controllers;

use App\Context;
use App\ThConceptLabel;
use App\ThLanguage;
use Illuminate\Http\Request;


class SearchController extends Controller {

    public function searchContextByName(Request $request) {
        $user = auth()->user();
        if(!$user->can('view_concepts')) {
            return response()->json([
                'error' => 'You do not have the permission to search for entities'
            ], 403);
        }
        $q = $request->query('q');
        $matches = Context::where('name', 'ilike', '%'.$q.'%')
            ->select('name', 'id', 'root_context_id')
            ->orderBy('name')
            ->get();
        $matches->each->append('ancestors');
        return response()->json($matches);
    }

    public function searchInThesaurus(Request $request) {
        $q = $request->query('q');
        $lang = 'de'; // TODO
        $langId = ThLanguage::where('short_name', $lang)->value('id');
        $matches = ThConceptLabel::where('label', 'ilike', '%'.$q.'%')
            ->where('language_id', $langId)
            ->with('concept')
            ->get();
        return response()->json($matches);
    }
}
