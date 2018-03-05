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

    public static function getChildren($url) {
        $id = self::where('concept_url', $url)->value('id');
        if(!isset($id)) return [];
        return DB::select("
            WITH RECURSIVE
            top AS (
                SELECT br.broader_id, br.narrower_id, c.concept_url
                FROM th_broaders br
                JOIN th_concept as c on c.id = br.narrower_id
                WHERE broader_id = $id
                UNION
                SELECT br.broader_id, br.narrower_id, c2.concept_url
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
