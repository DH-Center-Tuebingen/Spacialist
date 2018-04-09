<?php

namespace App;

use \DB;
use Illuminate\Database\Eloquent\Model;

class ThConcept extends Model
{
    protected $table = 'th_concept';
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'concept_url',
        'concept_scheme',
        'lasteditor',
    ];

    public static function getMap() {
        $lang = 'de'; // TODO
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

        $conceptMap = [];

        foreach ($concepts as $concept) {
            $url = $concept->concept_url;
            unset($concept->concept_url);
            $conceptMap[$url] = $concept;
        }

        return $conceptMap;
    }

    public static function getChildren($url) {
        $id = self::where('concept_url', $url)->value('id');
        if(!isset($id)) return [];
        return DB::select("
            WITH RECURSIVE
            top AS (
                SELECT br.broader_id, br.narrower_id, c.*
                FROM th_broaders br
                JOIN th_concept as c on c.id = br.narrower_id
                WHERE broader_id = $id
                UNION
                SELECT br.broader_id, br.narrower_id, c2.*
                FROM top t, th_broaders br
                JOIN th_concept as c2 on c2.id = br.narrower_id
                WHERE t.narrower_id = br.broader_id
            )
            SELECT *
            FROM top
        ");
    }

    public function labels() {
        return $this->hasMany('App\ThConceptLabel', 'concept_id');
    }

    public function narrowers() {
        return $this->belongsToMany('App\ThConcept', 'th_broaders', 'broader_id', 'narrower_id');
    }

    public function broaders() {
        return $this->belongsToMany('App\ThConcept', 'th_broaders', 'narrower_id', 'broader_id');
    }

    //TODO: this relationship is not working right now due to not referencing the id on ThConcept
    // as soon as id's are referenced this needs to be fixed
    public function files() {
        return $this->belongsToMany('App\File', 'photo_tags', 'concept_url', 'photo_id');
    }
}
