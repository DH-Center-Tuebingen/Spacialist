<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $table = 'photos';
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'modified',
        'cameraname',
        'photographer_id',
        'created',
        'thumb',
        'orientation',
        'copyright',
        'description',
        'lasteditor',
    ];

    public function contexts() {
        return $this->belongsToMany('App\Context', 'context_photos', 'photo_id', 'context_id');
    }

    //TODO: this relationship is not working right now due to not referencing the id on ThConcept
    // as soon as id's are referenced this needs to be fixed
    public function tags() {
        return $this->belongsToMany('App\ThConcept', 'photo_tags', 'photo_id', 'concept_url');
    }
}
