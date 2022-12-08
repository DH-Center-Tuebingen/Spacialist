<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
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

    public function getSelection() {
        switch ($this->datatype) {
            case 'string-sc':
            case 'string-mc':
            case 'epoch':
                return ThConcept::getChildren($this->thesaurus_root_url, $this->recursive);
            case 'table':
                // Only string-sc is allowed in tables
                $columns = Attribute::where('parent_id', $this->id)
                    ->where('datatype', 'string-sc')
                    ->get();
                $selection = [];
                foreach ($columns as $c) {
                    $selection[$c->id] = ThConcept::getChildren($c->thesaurus_root_url, $c->recursive);
                }
                return $selection;
            default:
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
