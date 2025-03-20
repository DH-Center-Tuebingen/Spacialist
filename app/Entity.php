<?php

namespace App;

use App\AttributeTypes\AttributeBase;
use App\AttributeTypes\SqlAttribute;
use App\Exceptions\AmbiguousValueException;
use App\Import\EntityImporter;
use App\Traits\CommentTrait;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        'name'           => 'required|string',
        'entity_type_id' => 'required|integer|exists:entity_types,id',
        'root_entity_id' => 'integer|exists:entities,id',
        'rank'           => 'integer|min:1',
    ];

    const patchRules = [
        'name'              => 'string',
        // 'entity_type_id'   => 'integer|exists:entity_types,id',
        // 'root_entity_id'   => 'integer|exists:entities,id',
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

    public function getAllMetadata() {
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

    public function getAllChildren(): array {
        $entity = $this;
        $entities = [];
        $parents = [$entity];
        $attributeCache = [];
        while($entity = array_shift($parents)) {
            // Add entity to array
            $data = [];
            $data['_name'] = $entity->name;
            $data['_parent'] = implode(EntityImporter::PARENT_DELIMITER, $entity->getAncestorsAttribute());
            $data['_entity_type'] = $entity->entity_type->thesaurus_concept->getActiveLocaleLabel();
            $data['_entity_type_id'] = $entity->entity_type_id;

            $entityData = $entity->getData();
            foreach($entityData as $aid => $attributeValue) {
                if(!array_key_exists($aid, $attributeCache)) {
                    $attributeCache[$aid] = Attribute::find($aid);
                }
                $actualAttribute = $attributeCache[$aid];
                if($actualAttribute->datatype == 'sql') {
                    $data[$aid] = $attributeValue['value'];
                } else {
                    $data[$aid] = AttributeBase::serializeExportData($attributeValue);
                }
            }
            $entities[] = $data;
            // Get all children and add them to the Queue
            $child_entities = Entity::getEntitiesByParent($entity->id);
            $parents = array_merge($parents, $child_entities->all());
        }
        return $entities;
    }

    public static function create($fields, $entityTypeId, $user, $rootEntityId = null, $rank = null) {
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
        if($isChild) {
            if(!isset($rank)) {
                $rank = self::where('root_entity_id', $rootEntityId)->max('rank') + 1;
            } else {
                $nextSiblings = self::where('root_entity_id', $rootEntityId)->where('rank', '>=', $rank)->get();
            }
            $entity->root_entity_id = $rootEntityId;
        } else {
            if(!isset($rank)) {
                $rank = self::whereNull('root_entity_id')->max('rank') + 1;
            } else {
                $nextSiblings = self::whereNull('root_entity_id')->where('rank', '>=', $rank)->get();
            }
        }
        $entity->rank = $rank;
        if(isset($nextSiblings) && count($nextSiblings) > 0) {
            foreach($nextSiblings as $sibling) {
                $sibling->rank = $sibling->rank + 1;
                $sibling->saveQuietly();
            }
        }

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

    private function moveOrFail(int | null $parentId) {
        if(isset($parentId)) {
            if($parentId == $this->id) {
                throw new \Exception('Cannot move entity to itself.');
            }

            $parentEntity = Entity::findOrFail($parentId);
            $parentEntityType = $parentEntity->entity_type;

            if(!$parentEntityType->sub_entity_types->contains($this->entity_type_id)) {
                throw new \Exception('This type is not an allowed sub-type.');
            }

            $this->root_entity_id = $parentId;
            $query = self::where('root_entity_id', $parentId);
        } else {
            if(!$this->entity_type->is_root) {
                throw new \Exception('This type is not an allowed root-type.');
            }

            $this->root_entity_id = null;
            $query = self::whereNull('root_entity_id');
        }
        return $query;
    }

    public static function patchRanks($rank, $id, $parent, $user) {
        $entity = Entity::find($id);
        $oldRank = $entity->rank;
        $entity->rank = $rank;
        $entity->user_id = $user->id;

        DB::beginTransaction();
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

        try {
            $query = $entity->moveOrFail($parent);
        } catch(\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        $newEntities = $query->where('rank', '>=', $rank)->get();

        foreach($newEntities as $nc) {
            $nc->rank++;
            $nc->saveQuietly();
        }

        $entity->save();
        DB::commit();
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

    public function getStaticAttributes() {
        $sqls = EntityAttribute::whereHas('attribute', function (Builder $q) {
            $q->where('datatype', 'sql');
        })
            ->where('entity_type_id', $this->entity_type_id);
        if(isset($aid)) {
            $sqls->where('attribute_id', $aid);
        }
        $sqls = $sqls->get();
    }

    public function getData($aid = null) {
        $attributes = [];
        if(isset($aid)) {
            try {
                Attribute::findOrFail($aid);
            } catch(ModelNotFoundException $e) {
                return response()->json([
                    'error' => __('This attribute does not exist'),
                ], 400);
            }
            $attributes = AttributeValue::whereHas('attribute')
                ->where('entity_id', $this->id)
                ->where('attribute_id', $aid)
                ->withModerated()
                ->get();
        } else {
            $attributes = AttributeValue::whereHas('attribute')
                ->where('entity_id', $this->id)
                ->withModerated()
                ->get();
        }

        $data = AttributeValue::generateObject($attributes);

        //// Somehow this is not working and I only receive the entity_type instead of
        //// the attributes array.
        // $entityType = $this->entity_type;
        // $attributes = $entityType->attributes;
        // info(json_encode($attributes));

        $sqls = EntityAttribute::whereHas('attribute', function (Builder $q) {
            $q->where('datatype', 'sql');
        })
            ->where('entity_type_id', $this->entity_type_id);
        if(isset($aid)) {
            $sqls->where('attribute_id', $aid);
        }
        $sqls = $sqls->get();

        foreach($sqls as $sql) {
            $value = SqlAttribute::execute($sql->attribute->text, $this->id);
            $data[$sql->attribute_id] = [
                'value' => $value,
            ];
        }

       return $data;
    }
}
