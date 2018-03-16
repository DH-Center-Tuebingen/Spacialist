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

    public function setRelationInfo($isRoot = false, $subTypes = []) {
        $this->is_root = $isRoot;
        ContextTypeRelation::where('parent_id', $this->id)->delete();
        foreach($subTypes as $type) {
            $relation = new ContextTypeRelation();
            $relation->parent_id = $this->id;
            $relation->child_id = $type;
            $relation->save();
        }
        $this->save();
    }

    public function layer() {
        return $this->hasOne('App\AvailableLayer');
    }

    public function contexts() {
        return $this->hasMany('App\Context');
    }

    public function attributes() {
        return $this->belongsToMany('App\Attribute', 'context_attributes')->withPivot('position');
    }

    public function sub_context_types() {
        return $this->belongsToMany('App\ContextType', 'context_type_relations', 'parent_id', 'child_id');
    }

    // This relationship is one-way, in case the has-relation is needed it must be implemented
    public function thesaurus_concept() {
        return $this->belongsTo('App\ThConcept', 'thesaurus_url', 'concept_url');
    }
}
