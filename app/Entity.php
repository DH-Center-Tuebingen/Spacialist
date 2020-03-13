<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;
use Spatie\Activitylog\Traits\LogsActivity;

class Entity extends Model
{
    use SearchableTrait;
    use LogsActivity;

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
        'rank',
    ];

    protected $appends = [
        'parentIds',
        'parentNames',
    ];

    protected $searchable = [
        'columns' => [
            'entities.name' => 10,
        ],
        'joins' => [

        ],
    ];

    protected static $logOnlyDirty = true;
    protected static $logFillable = true;
    protected static $logAttributes = ['id'];
    protected static $ignoreChangedAttributes = ['lasteditor'];

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

    public static function create($fields, $entityTypeId, $user, $rootEntityId = null) {
        $isChild = isset($rootEntityId);
        if($isChild) {
            $parentCtid = self::find($rootEntityId)->entity_type_id;
            $relation = EntityTypeRelation::where('parent_id', $parentCtid)
                ->where('child_id', $entityTypeId)->exists();
            if(!$relation) {
                return [
                    'type' => 'error',
                    'msg' => __('This type is not an allowed sub-type.'),
                    'code' => 400
                ];
            }
        } else {
            if(!EntityType::find($entityTypeId)->is_root) {
                return [
                    'type' => 'error',
                    'msg' => __('This type is not an allowed root-type.'),
                    'code' => 400
                ];
            }
        }

        $entity = new self();
        $rank;
        if($isChild) {
            $rank = self::where('root_entity_id', $rootEntityId)->max('rank') + 1;
            $entity->root_entity_id = $rootEntityId;
        } else {
            $rank = self::whereNull('root_entity_id')->max('rank') + 1;
        }
        $entity->rank = $rank;

        foreach($fields as $key => $value) {
            $entity->{$key} = $value;
        }
        $entity->entity_type_id = $entityTypeId;
        $entity->lasteditor = $user->name;
        $entity->save();

        // TODO workaround to get all (optional, not part of request) attributes
        $entity = self::find($entity->id);

        $serialAttributes = $entity->entity_type
                ->attributes()
                ->where('datatype', 'serial')
                ->get();
        foreach($serialAttributes as $s) {
            $nextValue = 1;
            $cleanedRegex = preg_replace('/(.*)(%\d*d)(.*)/i', '/$1(\d+)$3/i', $s->text);

            // get last added
            $lastEntity = self::where('entity_type_id', $entity->entity_type_id)
                ->orderBy('created_at', 'desc')
                ->skip(1)
                ->first();
            if(isset($lastEntity)) {
                $lastValue = AttributeValue::where('attribute_id', $s->id)
                    ->where('entity_id', $lastEntity->id)
                    ->first();
                if(isset($lastValue)) {
                    $nextValue = intval(preg_replace($cleanedRegex, '$1', $lastValue->str_val));
                    $nextValue++;
                }
            }

            self::addSerial($entity->id, $s->id, $s->text, $nextValue, $user->name);
        }

        $entity->children_count = 0;

        return [
            'type' => 'entity',
            'entity' => $entity
        ];
    }

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
        return $this->hasMany('App\Entity', 'root_entity_id')->orderBy('id');
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
        return $this->belongsToMany('App\Bibliography', 'references', 'entity_id', 'bibliography_id')->withPivot('description', 'attribute_id')->orderBy('references.attribute_id')->orderBy('references.bibliography_id');
    }

    public function attributes() {
        return $this->belongsToMany('App\Attribute', 'attribute_values')->withPivot('entity_val', 'str_val', 'int_val', 'dbl_val', 'dt_val', 'certainty', 'certainty_description', 'lasteditor', 'thesaurus_val', 'json_val', 'geography_val')->orderBy('attribute_values.attribute_id');
    }

    public function files() {
        return $this->belongsToMany('App\File', 'entity_files', 'entity_id', 'file_id')->orderBy('entity_files.file_id');
    }

    private function parents() {
        $ancestors = $this->where('id', '=', $this->id)->get();

        while ($ancestors->last() && $ancestors->last()->root_entity_id !== null) {
                $parent = $this->where('id', '=', $ancestors->last()->root_entity_id)->get();
                $ancestors = $ancestors->merge($parent);
            }
        return [
            'ids' => $ancestors->pluck('id')->all(),
            'names' => $ancestors->pluck('name')->all(),
        ];
    }

    public function getParentIdsAttribute() {
        return $this->parents()['ids'];
    }

    public function getParentNamesAttribute() {
        return $this->parents()['names'];
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
