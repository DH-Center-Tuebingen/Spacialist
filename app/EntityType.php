<?php

namespace App;

use App\EntityAttributePivot;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

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

    public function getActivitylogOptions() : LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['id'])
            ->logFillable()
            ->logOnlyDirty();
    }

    public function setRelationInfo($data) {
        if(array_key_exists('is_root', $data)) {
            $this->is_root = $data['is_root'];
        }
        if(array_key_exists('color', $data)) {
            $this->color = $data['color'];
        }
        if(array_key_exists('sub_entity_types', $data)) {
            EntityTypeRelation::where('parent_id', $this->id)->delete();
            foreach($data['sub_entity_types'] as $type) {
                $relation = new EntityTypeRelation();
                $relation->parent_id = $this->id;
                $relation->child_id = $type;
                $relation->save();
            }
        }
        $this->save();
    }

    // TODO move to Map Plugin
    // public function layer() {
    //     return $this->hasOne('App\AvailableLayer');
    // }

    public function entities() {
        return $this->hasMany('App\Entity')->orderBy('id');
    }

    public function attributes() {
        return $this->belongsToMany('App\Attribute', 'entity_attributes')
            ->withPivot(['position', 'depends_on', 'metadata', 'id'])
            ->orderBy('entity_attributes.position')
            ->using(EntityAttributePivot::class);
    }

    public function sub_entity_types() {
        return $this->belongsToMany('App\EntityType', 'entity_type_relations', 'parent_id', 'child_id')->orderBy('entity_type_relations.child_id');
    }

    // This relationship is one-way, in case the has-relation is needed it must be implemented
    public function thesaurus_concept() {
        return $this->belongsTo('App\ThConcept', 'thesaurus_url', 'concept_url');
    }
}
