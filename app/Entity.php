<?php

namespace App;

use App\Exceptions\AmbiguousValueException;
use App\Traits\CommentTrait;
use Illuminate\Database\Eloquent\Builder;
use App\AttributeTypes\AttributeBase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Nicolaslopezj\Searchable\SearchableTrait;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;

class Entity extends Model implements Searchable {
    use CommentTrait;
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
        'user_id',
        'geodata_id',
        'rank',
    ];

    protected $appends = [
        'parentIds',
        'parentNames',
        'attributeLinks',
    ];

    protected $casts = [
        'metadata' => 'json',
    ];

    protected $with = [
        'user',
    ];

    protected const searchCols = [
        'name' => 10,
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

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
            ->logOnly(['id'])
            ->logFillable()
            ->dontLogIfAttributesChangedOnly(['user_id'])
            ->logOnlyDirty();
    }

    public function getSearchResult(): SearchResult {
        return new SearchResult(
            $this,
            $this->name,
        );
    }

    public function getAllMetadata(){
        return [
            'creator' => $this->creator,
            'editors' => $this->editors,
            'metadata' => $this->metadata,
        ];
    }  
    
    public static function getSearchCols(): array {
        return array_keys(self::searchCols);
    }

    public static function getFromPath($path, $delimiter = "\\\\"): ?int {
        if(!isset($path)) {
            return null;
        }

        $parts = explode($delimiter, $path);
        $last = end($parts);
        $res = DB::select("
            WITH RECURSIVE path AS(
                SELECT id as final_id, id, root_entity_id, name as pathstr
                FROM entities
                WHERE name = ?
                UNION ALL
                SELECT final_id, e.id, e.root_entity_id, e.name || ? || p.pathstr
                FROM entities e
                INNER JOIN path p ON p.root_entity_id = e.id
            )
            SELECT final_id
            FROM path
            WHERE pathstr = ?
        ", ["$last", "$delimiter", "$path"]);
        if(count($res) > 1) {
            throw new AmbiguousValueException("Path '$path' is ambiguous");
        } else if(count($res) === 1) {
            return $res[0]->final_id;
        } else {
            return null;
        }
    }

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
        $entity->user_id = $user->id;
        $entity->save();

        // TODO workaround to get all (optional, not part of request) attributes
        $entity = self::find($entity->id);
        AttributeBase::onCreateHandler($entity, $user);
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

    public static function patchRanks($rank, $id, $parent, $user) {
        $entity = Entity::find($id);

        $hasParent = isset($parent);
        $oldRank = $entity->rank;
        $entity->rank = $rank;
        $entity->user_id = $user->id;

        $query;
        if(isset($entity->root_entity_id)) {
            $query = self::where('root_entity_id', $entity->root_entity_id);
        } else {
            $query = self::whereNull('root_entity_id');
        }
        $oldEntities = $query->where('rank', '>', $oldRank)->get();

        foreach($oldEntities as $oc) {
            $oc->rank--;
            $oc->saveQuietly();
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
            $nc->saveQuietly();
        }

        $entity->save();
    }

    public function child_entities() {
        return $this->hasMany('App\Entity', 'root_entity_id')->orderBy('id');
    }

    public function entity_type() {
        return $this->belongsTo('App\EntityType');
    }

    public function geodata() {
        return $this->belongsTo('App\Plugins\Map\App\Geodata');
    }

    public function root_entity() {
        return $this->belongsTo('App\Entity', 'root_entity_id');
    }

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function bibliographies() {
        return $this->belongsToMany('App\Bibliography', 'references', 'entity_id', 'bibliography_id')->withPivot('description', 'attribute_id')->orderBy('references.attribute_id')->orderBy('references.bibliography_id');
    }

    public function attributes() {
        return $this->belongsToMany('App\Attribute', 'attribute_values')->withPivot('entity_val', 'str_val', 'int_val', 'dbl_val', 'dt_val', 'certainty', 'user_id', 'thesaurus_val', 'json_val', 'geography_val')->orderBy('attribute_values.attribute_id');
    }

    public function files() {
        return $this->belongsToMany('App\File', 'entity_files', 'entity_id', 'file_id')->orderBy('entity_files.file_id');
    }

    public function getAttributeLinksAttribute() {
        $entityMcAttributes = Attribute::where('datatype', 'entity-mc')
            ->get()->pluck('id')->toArray();
        $links = AttributeValue::where('entity_val', $this->id)
            ->orWhere(function ($query) use ($entityMcAttributes) {
                $query->whereJsonContains('json_val', $this->id)
                    ->whereIn('attribute_id', $entityMcAttributes);
            })
            ->with(['attribute', 'entity' => function($eq) {
                $eq->without('user');
            }])
            ->get();
        $entities = [];
        foreach($links as $link) {
            $entity = $link->entity;
            $entities[] = [
                'id' => $entity->id,
                'name' => $entity->name,
                'entity_type_id' => $entity->entity_type_id,
                'attribute_url' => $link->attribute->thesaurus_url,
                'path' => array_reverse($entity->parentNames),
            ];
        }
        return $entities;
    }

    public function getEditorsAttribute() {
        $curr = $this;
        $assocAttrs = AttributeValue::with('attribute')->where('entity_id', $this->id)->get()->pluck('id');

        $causers = Activity::where(function (Builder $query) use ($curr) {
            $query->where('subject_id', $curr->id)
                ->where('subject_type', get_class($curr));
        })->orWhere(function (Builder $query) use ($assocAttrs) {
            $query->whereIn('subject_id', $assocAttrs)
                ->where('subject_type', (new AttributeValue())->getMorphClass());
        })
            ->groupBy('causer_id')
            ->select('causer_id as user_id')
            ->get();
        return $causers;
    }

    public function getCreatorAttribute() {
        $creator = Activity::where('subject_id', $this->id)
            ->where('subject_type', get_class($this))
            ->where('description', 'created')
            ->first();
        return isset($creator) ? $creator->causer_id : null;
    }

    public function parents() {
        $res = DB::select("
            WITH RECURSIVE getpath AS (
                SELECT id as path, name as pathn, root_entity_id as parent FROM entities WHERE id = $this->id
                UNION
                SELECT id, name, root_entity_id FROM getpath LEFT JOIN entities ON entities.id = getpath.parent WHERE entities.id = getpath.parent
            )
            SELECT path, pathn
            FROM getpath
        ");
        $ids = [];
        $names = [];
        foreach($res as $path) {
            $ids[] = $path->path;
            $names[] = $path->pathn;
        }

        return [
            'ids' => $ids,
            'names' => $names,
        ];
    }

    public function getParentIdsAttribute() {
        // $res = DB::select("
        //     WITH RECURSIVE getpath AS (
        //         SELECT id as path, name as pathn, root_entity_id as parent FROM entities WHERE id = $this->id
        //         UNION
        //         SELECT id, name, root_entity_id FROM getpath LEFT JOIN entities ON entities.id = getpath.parent WHERE entities.id = getpath.parent
        //     )
        //     SELECT path
        //     FROM getpath
        // ");
        // $filtered = array_map(function($elem) {
        //     return $elem->path;
        // }, $res);
        // return $filtered;
        return $this->parents()['ids'];
    }

    public function getParentNamesAttribute() {
        // $res = DB::select("
        //     WITH RECURSIVE getpath AS (
        //         SELECT id as path, name as pathn, root_entity_id as parent FROM entities WHERE id = $this->id
        //         UNION
        //         SELECT id, name, root_entity_id FROM getpath LEFT JOIN entities ON entities.id = getpath.parent WHERE entities.id = getpath.parent
        //     )
        //     SELECT pathn
        //     FROM getpath
        // ");
        // $filtered = array_map(function($elem) {
        //     return $elem->pathn;
        // }, $res);
        // return $filtered;
        return $this->parents()['names'];
    }

    public function getAncestorsAttribute() {
        $parents = array_reverse($this->getParentNamesAttribute());
        array_pop($parents);
        return $parents;
    }

    public function getData() {
        $attributes = AttributeValue::whereHas('attribute', function (Builder $q) {
            $q->where('datatype', '!=', 'sql');
        })
            ->where('entity_id', $this->id)
            ->withModerated()
            ->get();

        $data = [];
        foreach($attributes as $a) {
            switch($a->attribute->datatype) {
                case 'string-sc':
                    $a->thesaurus_val = ThConcept::where('concept_url', $a->thesaurus_val)->first();
                    break;
                case 'entity':
                    $a->name = Entity::find($a->entity_val)->name;
                    break;
                case 'entity-mc':
                    $names = [];
                    foreach(json_decode($a->json_val) as $dec) {
                        $names[] = Entity::find($dec)->name;
                    }
                    $a->name = $names;
                    break;
                default:
                    break;
            }
            $value = $a->getValue();
            if($a->moderation_state == 'pending-delete') {
                $a->value = [];
                $a->original_value = $value;
            } else {
                $a->value = $value;
            }
            if(isset($data[$a->attribute_id])) {
                $oldAttr = $data[$a->attribute_id];
                // check if stored entry is moderated one
                // if so, add current value as original value
                // otherwise, set stored entry as original value
                if(isset($oldAttr->moderation_state)) {
                    $oldAttr->original_value = $value;
                    $a = $oldAttr;
                } else {
                    $a->original_value = $oldAttr->value;
                }
            }
            $data[$a->attribute_id] = $a;
        }

        return $data;
    }
}
