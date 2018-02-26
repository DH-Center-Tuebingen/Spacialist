<?php

namespace App\Http\Controllers;

use App\Context;
use App\ThConceptLabel;
use App\ThLanguage;
use Illuminate\Http\Request;


class SearchController extends Controller {

    public function searchContextByName(Request $request) {
        $q = $request->query('q');
        $matches = Context::where('name', 'ilike', '%'.$q.'%')
            ->select('name', 'id')
            ->orderBy('name')
            ->get();
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
