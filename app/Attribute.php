<?php

namespace App;

use App\AttributeTypes\AttributeBase;
use Illuminate\Database\Eloquent\Model;
use MStaack\LaravelPostgis\Geometries\Geometry;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Attribute extends Model
{
    use LogsActivity;
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'thesaurus_url',
        'thesaurus_root_url',
        'datatype',
        'text',
        'parent_id',
        'recursive',
        'root_attribute_id',
    ];

    public function getActivitylogOptions() : LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['id'])
            ->logFillable()
            ->logOnlyDirty();
    }

    public function getEntityAttributeValueName() {
        $name = null;
        switch($this->datatype) {
            case 'entity':
                $name = Entity::find($this->pivot->entity_val)->name;
                break;
            case 'entity-mc':
                $value = [];
                foreach(json_decode($this->pivot->json_val) as $dec) {
                    $value[] = Entity::find($dec)->name;
                }
                $name = $value;
                break;
        }
        return $name;
    }

    public function getAttributeValueFromEntityPivot()
    {
        switch ($this->datatype) {
            case 'string-sc':
                $this->pivot->thesaurus_val = ThConcept::where('concept_url', $this->pivot->thesaurus_val)->first();
                break;
            default:
                break;
        }
        return $this->pivot->str_val ??
            $this->pivot->int_val ??
            $this->pivot->dbl_val ??
            $this->pivot->entity_val ??
            $this->pivot->thesaurus_val ??
            json_decode($this->pivot->json_val) ??
            $this->pivot->dt_val ??
            Geometry::fromWKB($this->pivot->geography_val)->toWKT();
    }

    public function getSelection() {
        $attributeClass = AttributeBase::getMatchingClass($this->datatype);
        if($attributeClass !== false && $attributeClass::getHasSelection()) {
            return $attributeClass::getSelection($this);
        } else {
            return null;
        }
    }

    public function children() {
        return $this->hasMany('App\Attribute', 'parent_id');
    }

    public function entities() {
        return $this->belongsToMany('App\Entity', 'attribute_values')->withPivot('entity_val', 'str_val', 'int_val', 'dbl_val', 'dt_val', 'certainty', 'user_id', 'thesaurus_val', 'json_val', 'geography_val');
    }

    public function entity_types() {
        return $this->belongsToMany('App\EntityType', 'entity_attributes')->withPivot('position');
    }

    public function parent() {
        return $this->belongsTo('App\Attribute', 'parent_id');
    }

    // This relationship is one-way, in case the has-relation is needed it must be implemented
    public function thesaurus_root_concept() {
        return $this->belongsTo('App\ThConcept', 'thesaurus_root_url', 'concept_url');
    }

    // This relationship is one-way, in case the has-relation is needed it must be implemented
    public function thesaurus_concept() {
        return $this->belongsTo('App\ThConcept', 'thesaurus_url', 'concept_url');
    }

}
