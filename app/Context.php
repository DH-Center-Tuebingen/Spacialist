<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Context extends Model
{
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'context_type_id',
        'root_context_id',
        'name',
        'lasteditor',
        'geodata_id',
    ];

    const rules = [
        'name'              => 'required|string',
        'context_type_id'   => 'required|integer|exists:context_types,id',
        'root_context_id'   => 'integer|exists:contexts,id',
        'geodata_id'        => 'integer|exists:geodata,id'
    ];

    const patchRules = [
        'name'              => 'string',
        // 'context_type_id'   => 'integer|exists:context_types,id',
        // 'root_context_id'   => 'integer|exists:contexts,id',
        // 'geodata_id'        => 'integer|exists:geodata,id'
    ];

    public static function getEntitiesByParent($id = null) {
        $entities = self::withCount(['child_contexts as children_count']);
        if(!isset($id)) {
            $entities->whereNull('root_context_id');
        } else {
            $entities->where('root_context_id', $id);
        }
        return $entities->get();
    }

    public function child_contexts() {
        return $this->hasMany('App\Context', 'root_context_id');
    }

    public function context_type() {
        return $this->belongsTo('App\ContextType');
    }

    public function geodata() {
        return $this->belongsTo('App\Geodata');
    }

    public function root_context() {
        return $this->belongsTo('App\Context', 'root_context_id');
    }

    public function literatures() {
        return $this->belongsToMany('App\Literature', 'sources')->withPivot('description', 'attribute_id');
    }

    public function attributes() {
        return $this->belongsToMany('App\Attribute', 'attribute_values')->withPivot('context_val', 'str_val', 'int_val', 'dbl_val', 'dt_val', 'possibility', 'possibility_description', 'lasteditor', 'thesaurus_val', 'json_val', 'geography_val');
    }

    public function files() {
        return $this->belongsToMany('App\File', 'context_photos', 'context_id', 'photo_id');
    }
}
