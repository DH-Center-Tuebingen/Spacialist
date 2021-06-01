<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class EntityType extends Model
{
    use LogsActivity;

    protected $table = 'entity_types';
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'thesaurus_url',
        'is_root',
    ];

    const patchRules = [
        'thesaurus_url' => 'string',
    ];

    protected static $logOnlyDirty = true;
    protected static $logFillable = true;
    protected static $logAttributes = ['id'];

    public function setRelationInfo($isRoot = false, $subTypes = []) {
        $this->is_root = $isRoot;
        EntityTypeRelation::where('parent_id', $this->id)->delete();
        foreach($subTypes as $type) {
            $relation = new EntityTypeRelation();
            $relation->parent_id = $this->id;
            $relation->child_id = $type;
            $relation->save();
        }
        $this->save();
    }

    public function layer() {
        return $this->hasOne('App\AvailableLayer');
    }

    public function entities() {
        return $this->hasMany('App\Entity')->orderBy('id');
    }

    public function attributes() {
        return $this->belongsToMany('App\Attribute', 'entity_attributes')->withPivot(['position', 'depends_on'])->orderBy('entity_attributes.position');
    }

    public function sub_entity_types() {
        return $this->belongsToMany('App\EntityType', 'entity_type_relations', 'parent_id', 'child_id')->orderBy('entity_type_relations.child_id');
    }

    // This relationship is one-way, in case the has-relation is needed it must be implemented
    public function thesaurus_concept() {
        return $this->belongsTo('App\ThConcept', 'thesaurus_url', 'concept_url');
    }
}
