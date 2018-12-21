<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

class Entity extends Model
{
    use SearchableTrait;

    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'entity_type_id',
        'root_entity_id',
        'name',
        'lasteditor',
        'geodata_id',
    ];

    protected $appends = ['parentIds'];

    protected $searchable = [
        'columns' => [
            'entities.name' => 10,
        ],
        'joins' => [

        ],
    ];

    const rules = [
        'name'              => 'required|string',
        'entity_type_id'   => 'required|integer|exists:entity_types,id',
        'root_entity_id'   => 'integer|exists:entities,id',
        'geodata_id'        => 'integer|exists:geodata,id'
    ];

    const patchRules = [
        'name'              => 'string',
        // 'entity_type_id'   => 'integer|exists:entity_types,id',
        // 'root_entity_id'   => 'integer|exists:entities,id',
        // 'geodata_id'        => 'integer|exists:geodata,id'
    ];

    public static function getEntitiesByParent($id = null) {
        $entities = self::withCount(['child_entities as children_count']);
        if(!isset($id)) {
            $entities->whereNull('root_entity_id');
        } else {
            $entities->where('root_entity_id', $id);
        }
        return $entities->orderBy('rank')->get();
    }

    public static function patchRanks($rank, $id, $parent = null, $user) {
        $entity = Entity::find($id);

        $hasParent = isset($parent);
        $oldRank = $entity->rank;
        $entity->rank = $rank;
        $entity->lasteditor = $user->name;

        $query;
        if(isset($entity->root_entity_id)) {
            $query = self::where('root_entity_id', $entity->root_entity_id);
        } else {
            $query = self::whereNull('root_entity_id');
        }
        $oldEntities = $query->where('rank', '>', $oldRank)->get();

        foreach($oldEntities as $oc) {
            $oc->rank--;
            $oc->save();
        }

        $query = null;
        if($hasParent) {
            $entity->root_entity_id = $parent;
            $query = self::where('root_entity_id', $parent);
        } else {
            $entity->root_entity_id = null;
            $query = self::whereNull('root_entity_id');
        }
        $newEntities = $query->where('rank', '>=', $rank)->get();

        foreach($newEntities as $nc) {
            $nc->rank++;
            $nc->save();
        }

        $entity->save();
    }

    public static function addSerial($eid, $aid, $text, $ctr, $username) {
        $av = new AttributeValue();
        $av->entity_id = $eid;
        $av->attribute_id = $aid;
        $av->str_val = sprintf($text, $ctr);
        $av->lasteditor = $username;
        $av->save();
    }

    public function child_entities() {
        return $this->hasMany('App\Entity', 'root_entity_id');
    }

    public function entity_type() {
        return $this->belongsTo('App\EntityType');
    }

    public function geodata() {
        return $this->belongsTo('App\Geodata');
    }

    public function root_entity() {
        return $this->belongsTo('App\Entity', 'root_entity_id');
    }

    public function bibliographies() {
        return $this->belongsToMany('App\Bibliography', 'references', 'entity_id', 'bibliography_id')->withPivot('description', 'attribute_id');
    }

    public function attributes() {
        return $this->belongsToMany('App\Attribute', 'attribute_values')->withPivot('entity_val', 'str_val', 'int_val', 'dbl_val', 'dt_val', 'certainty', 'certainty_description', 'lasteditor', 'thesaurus_val', 'json_val', 'geography_val');
    }

    public function files() {
        return $this->belongsToMany('App\File', 'entity_files', 'entity_id', 'file_id');
    }

    private function parentIds() {
        $ancestors = $this->where('id', '=', $this->id)->get();

        while ($ancestors->last() && $ancestors->last()->root_entity_id !== null) {
                $parent = $this->where('id', '=', $ancestors->last()->root_entity_id)->get();
                $ancestors = $ancestors->merge($parent);
            }
        return $ancestors->pluck('id')->all();
    }

    public function getParentIdsAttribute() {
        return $this->parentIds();
    }

    private function ancestors() {
        $ancestors = $this->where('id', '=', $this->root_entity_id)->get();

        while ($ancestors->last() && $ancestors->last()->root_entity_id !== null) {
                $parent = $this->where('id', '=', $ancestors->last()->root_entity_id)->get();
                $ancestors = $ancestors->merge($parent);
            }
        return $ancestors->reverse()->pluck('name')->all();
    }

    public function getAncestorsAttribute() {
        return $this->ancestors();
    }
}
