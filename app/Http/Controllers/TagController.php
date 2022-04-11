<?php

namespace App\Http\Controllers;

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
        $tagObj = Preference::where('label', 'prefs.tag-root')
            ->value('default_value');
        $tagUri = json_decode($tagObj)->uri;
        $tags = \DB::select("
            WITH RECURSIVE
            top AS (
                SELECT br.narrower_id as id, c2.concept_url
                FROM th_broaders br
                JOIN th_concept c ON c.id = br.broader_id
                JOIN th_concept c2 ON c2.id = br.narrower_id
                WHERE c.concept_url = '$tagUri'
                UNION
                SELECT br.narrower_id as id, c.concept_url
                FROM top t, th_broaders br
                JOIN th_concept c ON c.id = br.narrower_id
                WHERE t.id = br.broader_id
            )
            SELECT *
            FROM top
            ORDER BY id
        ");
        return response()->json($tags);
    }

    // POST

    // PATCH

    // DELETE
}
