<?php

namespace App;

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
