<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class ThesaurusController extends Controller
{
    public function getConcepts($lang) {
        $user = \Auth::user();
        if(!$user->can('view_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $concepts = \DB::select(\DB::raw("
            WITH summary AS
            (
                SELECT th_concept.id, concept_url, is_top_concept, label, language_id, th_language.short_name,
                ROW_NUMBER() OVER
                (
                    PARTITION BY th_concept.id
                    ORDER BY th_concept.id, short_name != '$lang', concept_label_type
                ) AS rk
                FROM th_concept
                JOIN th_concept_label ON th_concept_label.concept_id = th_concept.id
                JOIN th_language ON language_id = th_language.id
            )
            SELECT id, concept_url, is_top_concept, label, language_id, short_name
            FROM summary s
            WHERE s.rk = 1"));

        $data = [];

        foreach ($concepts as $concept) {
            $url = $concept->concept_url;
            unset($concept->concept_url);
            $data[$url] = $concept;
        }

        return response()->json([
            'data' => $data
        ]);
    }
}
