<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

class Context extends Model
{
    use SearchableTrait;

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

    protected $searchable = [
        'columns' => [
            'contexts.name' => 10,
        ],
        'joins' => [

        ],
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
        return $entities->orderBy('rank')->get();
    }

    public static function patchRanks($rank, $id, $parent = null, $user) {
        $context = Context::find($id);

        $hasParent = isset($parent);
        $oldRank = $context->rank;
        $context->rank = $rank;
        $context->lasteditor = $user->name;

        $query;
        if(isset($context->root_context_id)) {
            $query = self::where('root_context_id', $context->root_context_id);
        } else {
            $query = self::whereNull('root_context_id');
        }
        $oldContexts = $query->where('rank', '>', $oldRank)->get();

        foreach($oldContexts as $oc) {
            $oc->rank--;
            $oc->save();
        }

        $query = null;
        if($hasParent) {
            $context->root_context_id = $parent;
            $query = self::where('root_context_id', $parent);
        } else {
            $context->root_context_id = null;
            $query = self::whereNull('root_context_id');
        }
        $newContexts = $query->where('rank', '>=', $rank)->get();

        foreach($newContexts as $nc) {
            $nc->rank++;
            $nc->save();
        }

        $context->save();
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
        return $this->belongsToMany('App\Bibliography', 'sources', 'context_id', 'literature_id')->withPivot('description', 'attribute_id');
    }

    public function attributes() {
        return $this->belongsToMany('App\Attribute', 'attribute_values')->withPivot('context_val', 'str_val', 'int_val', 'dbl_val', 'dt_val', 'possibility', 'possibility_description', 'lasteditor', 'thesaurus_val', 'json_val', 'geography_val');
    }

    public function files() {
        return $this->belongsToMany('App\File', 'context_photos', 'context_id', 'photo_id');
    }

    private function path() {
        $ancestors = $this->where('id', '=', $this->id)->get();

        while ($ancestors->last() && $ancestors->last()->root_context_id !== null) {
                $parent = $this->where('id', '=', $ancestors->last()->root_context_id)->get();
                $ancestors = $ancestors->merge($parent);
            }
        return $ancestors->reverse()->pluck('rank')->map(function($item, $key) {
            return $item - 1;
        })->all();
    }

    public function getPathAttribute() {
        return $this->path();
    }

    private function ancestors() {
        $ancestors = $this->where('id', '=', $this->root_context_id)->get();

        while ($ancestors->last() && $ancestors->last()->root_context_id !== null) {
                $parent = $this->where('id', '=', $ancestors->last()->root_context_id)->get();
                $ancestors = $ancestors->merge($parent);
            }
        return $ancestors->reverse()->pluck('name')->all();
    }

    public function getAncestorsAttribute() {
        return $this->ancestors();
    }
}
