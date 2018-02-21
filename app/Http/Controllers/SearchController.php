<?php

namespace App\Http\Controllers;

use App\Context;
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
}
