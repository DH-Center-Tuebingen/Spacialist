<?php

namespace App\Http\Controllers;

use App\Globals;
use Illuminate\Support\Facades\DB;
use App\Preference;

class TagController extends Controller {
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    // GET

    public function getAll() {
        $user = auth()->user();
        if(!$user->can('thesaurus_read')) {
            return response()->json([
                'error' => __('You do not have the permission to get tags')
            ], 403);
        }

        $tags = Globals::getTags();

        return response()->json($tags);
    }

    // POST

    // PATCH

    // DELETE
}
