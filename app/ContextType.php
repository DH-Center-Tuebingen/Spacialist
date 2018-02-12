<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContextType extends Model
{
    protected $table = 'context_types';
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'thesaurus_url',
        'type',
    ];

    public function layer() {
        return $this->hasOne('App\AvailableLayer');
    }

    public function contexts() {
        return $this->hasMany('App\Context');
    }

    public function attributes() {
        return $this->belongsToMany('App\Attribute', 'context_attributes')->withPivot('position');
    }

    // This relationship is one-way, in case the has-relation is needed it must be implemented
    public function thesaurus_concept() {
        return $this->belongsTo('App\ThConcept', 'thesaurus_url', 'concept_url');
    }
}
