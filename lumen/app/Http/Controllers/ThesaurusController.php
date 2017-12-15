<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Helpers;
use App\ThConcept;
use App\ThConceptLabel;

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

    private static function getConceptForLabel($label, $lang) {
        $user = \Auth::user();
        if(!$user->can('view_concepts_th')) {
            return null;
        }
        $thConcept = 'th_concept';
        $thLabel = 'th_concept_label';
        $concept = \DB::select(\DB::raw("
            WITH summary AS
            (
                SELECT c.concept_url, c.id,
                ROW_NUMBER() OVER
                (
                    PARTITION BY c.id
                    ORDER BY c.id, short_name != '$lang', concept_label_type
                ) AS rk
                FROM $thLabel as l
                JOIN th_language as lng ON l.language_id = lng.id
                JOIN $thConcept as c ON l.concept_id = c.id
                WHERE l.label = '$label'
            )
            SELECT concept_url, id
            FROM summary s
            WHERE s.rk = 1"));
        return $concept;
    }

    public static function getConceptUrlForLabel($label, $lang = 'de') {
        $concept = self::getConceptForLabel($label, $lang);
        if(isset($concept) && !empty($concept)) return $concept[0]->concept_url;
        return null;
    }

    public static function getConceptIdForLabel($label, $lang = 'de') {
        $concept = self::getConceptForLabel($label, $lang);
        if(isset($concept) && !empty($concept)) return $concept[0]->id;
        return null;
    }

    public static function createConcept($projName, $label, $user, $languageId, $isTopConcept, $labelType) {
        $ts = date("YmdHis");
        $labelUrl = Helpers::labelToUrlPart($label);
        $projName = Helpers::labelToUrlPart($projName);
        $concept = new ThConcept();
        $concept->concept_url = "https://spacialist.escience.uni-tuebingen.de/$projName/$labelUrl#$ts";
        $concept->concept_scheme = "https://spacialist.escience.uni-tuebingen.de/schemata#newScheme";
        $concept->lasteditor = $user['name'];
        $concept->is_top_concept = $isTopConcept;
        $concept->save();
        $thLabel = new ThConceptLabel();
        $thLabel->label = $label;
        $thLabel->concept_id = $concept->id;
        $thLabel->language_id = $languageId;
        $thLabel->concept_label_type = $labelType;
        $thLabel->lasteditor = $user['name'];
        $thLabel->save();
        return $concept;
    }
}
