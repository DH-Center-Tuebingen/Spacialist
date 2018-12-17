<?php

namespace App\Http\Controllers;

use App\Attribute;
use App\AttributeValue;
use App\Entity;
use App\EntityAttribute;
use App\EntityType;
use App\EntityTypeRelation;
use App\ThConcept;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EntityController extends Controller {
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    // GET

    public function getTopEntities() {
        $user = auth()->user();
        if(!$user->can('view_concepts')) {
            return response()->json([], 204);
        }
        $roots = Entity::getEntitiesByParent(null);

        return response()->json($roots);
    }

    public function getEntity($id) {
        $user = auth()->user();
        if(!$user->can('view_concepts')) {
            return response()->json([
                'error' => __('You do not have the permission to get a specific entity')
            ], 403);
        }
        try {
            $entity = Entity::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist')
            ], 400);
        }

        return response()->json($entity);
    }

    public function getDataForEntityType($ctid, $aid) {
        $user = auth()->user();
        if(!$user->can('view_concepts')) {
            return response()->json([
                'error' => __('You do not have the permission to get an entity\'s data')
            ], 403);
        }
        try {
            $entityType = EntityType::findOrFail($ctid);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity type does not exist')
            ], 400);
        }
        $entities = Entity::where('entity_type_id', $ctid)->get();
        $entityIds = $entities->pluck('id')->toArray();
        $values = AttributeValue::with(['attribute'])
            ->whereIn('entity_id', $entityIds)
            ->where('attribute_id', $aid)
            ->get();
        $data = [];
        foreach($values as $value) {
            switch($value->attribute->datatype) {
                case 'string-sc':
                    $value->thesaurus_val = ThConcept::where('concept_url', $value->thesaurus_val)->first();
                    break;
                case 'entity':
                    $value->name = Entity::find($value->entity_val)->name;
                    break;
                default:
                    break;
            }
            $value->value = $value->getValue();
            $data[$value->entity_id] = $value;
        }

        return response()->json($data);
    }

    public function getData($id, $aid = null) {
        $user = auth()->user();
        if(!$user->can('view_concepts')) {
            return response()->json([
                'error' => __('You do not have the permission to get an entity\'s data')
            ], 403);
        }
        try {
            $entity = Entity::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist')
            ], 400);
        }
        if(isset($aid)) {
            $attributes = AttributeValue::with(['attribute'])
                ->where('entity_id', $id)
                ->where('attribute_id', $aid)
                ->get();
        } else {
            $attributes = AttributeValue::with(['attribute'])
                ->where('entity_id', $id)
                ->get();
        }

        $data = [];
        foreach($attributes as $a) {
            switch($a->attribute->datatype) {
                case 'string-sc':
                    $a->thesaurus_val = ThConcept::where('concept_url', $a->thesaurus_val)->first();
                    break;
                case 'entity':
                    $a->name = Entity::find($a->entity_val)->name;
                    break;
                default:
                    break;
            }
            $value = $a->getValue();
            $a->value = $value;
            $data[$a->attribute_id] = $a;
        }

        $sqls = EntityAttribute::join('attributes', 'attributes.id', '=', 'attribute_id')
            ->where('entity_type_id', $entity->entity_type_id)
            ->where('datatype', 'sql')
            ->get();
        foreach($sqls as $sql) {
            // if entity_id is referenced several times
            // add an incrementing counter, so the
            // references are unique (required by PDO)
            $cnt = substr_count($sql->text, ':entity_id');
            if($cnt > 1) {
                $safes = [];
                for($i=0; $i<$cnt; $i++) {
                    $safes[':entity_id_'.$i] = $id;
                }
                $i = 0;
                $text = preg_replace_callback('/:entity_id/', function($matches) use (&$i) {
                    return $matches[0].'_'.$i++;
                }, $sql->text);
            } else {
                $text = $sql->text;
                $safes = [
                    ':entity_id' => $id
                ];
            }
            $raw = \DB::raw($text);
            $sqlValue = \DB::select($text, $safes);
            // Check if only one result exists
            if(count($sqlValue) === 1) {
                // Get all column indices (keys) using the first row
                $valueKeys = array_keys(get_object_vars($sqlValue[0]));
                // Check if also only one key/column exists
                if(count($valueKeys) === 1) {
                    // If only one row and one column exist,
                    // return plain value instead of array
                    $firstKey = $valueKeys[0];
                    $sqlValue = $sqlValue[0]->{$firstKey};
                }
            }
            $data[$sql->attribute_id] = [
                'value' => $sqlValue
            ];
        }

        return response()->json($data);
    }

    public function getParentIds($id) {
        $user = auth()->user();
        if(!$user->can('view_concepts')) {
            return response()->json([
                'error' => __('You do not have the permission to get an entity\'s parent id\'s')
            ], 403);
        }

        try {
            $entity = Entity::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist')
            ], 400);
        }
        return response()->json($entity->parentIds);
    }

    public function getChildren($id) {
        $user = auth()->user();
        if(!$user->can('view_concepts')) {
            return response()->json([
                'error' => __('You do not have the permission to get an entity\'s successors')
            ], 403);
        }
        try {
            $entity = Entity::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist')
            ], 400);
        }
        $children = Entity::where('root_entity_id', $id)->get();
        return response()->json($children);
    }

    public function getEntitiesByParent($id) {
        return Entity::getEntitiesByParent($id);
    }

    // POST

    public function addEntity(Request $request) {
        $user = auth()->user();
        if(!$user->can('create_concepts')) {
            return response()->json([
                'error' => __('You do not have the permission to add a new entity')
            ], 403);
        }
        $this->validate($request, Entity::rules);

        $isChild = $request->has('root_entity_id');
        $rcid = $request->get('root_entity_id');

        if($isChild) {
            $parentCtid = Entity::find($rcid)->entity_type_id;
            $relation = EntityTypeRelation::where('parent_id', $parentCtid)
                ->where('child_id', $request->get('entity_type_id'))->get();
            if(!isset($relation)) {
                return response()->json([
                    'error' => __('This type is not an allowed sub-type.')
                ], 400);
            }
        } else {
            if(!EntityType::find($request->get('entity_type_id'))->is_root) {
                return response()->json([
                    'error' => __('This type is not an allowed root-type.')
                ], 400);
            }
        }

        $entity = new Entity();
        $rank;
        if($isChild) {
            $rank = Entity::where('root_entity_id', '=', $rcid)->max('rank') + 1;
        } else {
            $rank = Entity::whereNull('root_entity_id')->max('rank') + 1;
        }
        $entity->rank = $rank;

        foreach($request->only(array_keys(Entity::rules)) as $key => $value) {
            $entity->{$key} = $value;
        }
        $entity->lasteditor = $user->name;
        $entity->save();

        $serialAttributes = $entity->entity_type
                ->attributes()
                ->where('datatype', 'serial')
                ->get();
        foreach($serialAttributes as $s) {
            $cleanedRegex = preg_replace('/(.*)(%\d*d)(.*)/i', '/$1(\d+)$3/i', $s->text);

            // get last added
            $lastEntity = Entity::where('entity_type_id', $entity->entity_type_id)
                ->orderBy('created_at', 'desc')
                ->skip(1)
                ->first();
            $lastValue = AttributeValue::where('attribute_id', $s->id)
                ->where('entity_id', $lastEntity->id)
                ->first();
            $nextValue = intval(preg_replace($cleanedRegex, '$1', $lastValue->str_val));
            $nextValue++;


            Entity::addSerial($entity->id, $s->id, $s->text, $nextValue, $user->name);
        }

        return response()->json($entity, 201);
    }

    // PATCH

    public function patchAttributes($id, Request $request) {
        $user = auth()->user();
        if(!$user->can('duplicate_edit_concepts')) {
            return response()->json([
                'error' => __('You do not have the permission to modify an entity\' data')
            ], 403);
        }

        try {
            $entity = Entity::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist')
            ], 400);
        }

        foreach($request->request as $pid => $patch) {
            $op = $patch['op'];
            $aid = $patch['params']['aid'];
            $attr = Attribute::find($aid);
            switch($op) {
                case 'remove':
                    $attrval = AttributeValue::where([
                        ['entity_id', '=', $id],
                        ['attribute_id', '=', $aid]
                    ])->first();
                    $attrval->delete();
                    return response()->json(null, 204);
                case 'add':
                    $value = $patch['value'];
                    $attrval = new AttributeValue();
                    $attrval->entity_id = $id;
                    $attrval->attribute_id = $aid;
                    break;
                case 'replace':
                    $value = $patch['value'];
                    $attrval = AttributeValue::where([
                        ['entity_id', '=', $id],
                        ['attribute_id', '=', $aid]
                    ])->first();
                    break;
                default:
                    return response()->json([
                        'error' => __('Unknown operation')
                    ], 400);
            }
            switch($attr->datatype) {
                // for primitive types: just save them to the db
                case 'stringf':
                case 'string':
                case 'geography':
                    $attrval->str_val = $value;
                    break;
                case 'double':
                    $attrval->dbl_val = $value;
                    break;
                case 'boolean':
                case 'percentage':
                case 'integer':
                    $attrval->int_val = $value;
                    break;
                case 'date':
                    $attrval->dt_val = $value;
                    break;
                case 'string-sc':
                    $thesaurus_url = $value['concept_url'];
                    $attrval->thesaurus_val = $thesaurus_url;
                    break;
                case 'string-mc':
                    $thesaurus_urls = [];
                    foreach($value as $val) {
                        $thesaurus_urls[] = [
                            "concept_url" => $val['concept_url'],
                            "id" => $val['id']
                        ];
                    }
                    $attrval->json_val = json_encode($thesaurus_urls);
                    break;
                case 'epoch':
                case 'dimension':
                case 'list':
                case 'table':
                    $attrval->json_val = json_encode($value);
                    break;
                case 'entity':
                    $attrval->entity_val = $value;
                    break;
            }
            $attrval->lasteditor = $user->name;
            $attrval->save();
        }

        // Save model if lasteditor changed
        // Only update timestamps otherwise
        $entity->lasteditor = $user->name;
        if($entity->isDirty()) {
            $entity->save();
        } else {
            $entity->touch();
        }

        return response()->json($entity);
    }

    public function patchAttribute($id, $aid, Request $request) {
        $user = auth()->user();
        if(!$user->can('duplicate_edit_concepts')) {
            return response()->json([
                'error' => __('You do not have the permission to modify an entity\'s data')
            ], 403);
        }
        $this->validate($request, AttributeValue::patchRules);

        try {
            $entity = Entity::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist')
            ], 400);
        }
        try {
            $attribute = Attribute::findOrFail($aid);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This attribute does not exist')
            ], 400);
        }

        $attrs = AttributeValue::where('entity_id', $id)
            ->where('attribute_id', $aid)
            ->get();
        $values = $request->only(array_keys(AttributeValue::patchRules));
        foreach($attrs as $a) {
            $a->patch($values);
        }

        return response()->json(null, 204);
    }

    public function patchName($id, Request $request) {
        $user = auth()->user();
        if(!$user->can('duplicate_edit_concepts')) {
            return response()->json([
                'error' => __('You do not have the permission to modify an entity\'s data')
            ], 403);
        }
        $this->validate($request, [
            'name' => 'required|string'
        ]);

        try {
            $entity = Entity::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist')
            ], 400);
        }

        $entity->name = $request->get('name');
        $entity->save();

        return response()->json(null, 204);
    }

    public function moveEntity(Request $request, $id) {
        $user = auth()->user();
        if(!$user->can('delete_move_concepts')) {
            return response()->json([
                'error' => __('You do not have the permission to modify an entity')
            ], 403);
        }
        $this->validate($request, [
            'rank' => 'required|integer',
            'parent_id' => 'nullable|integer|exists:entities,id|different:id',
        ]);

        $rank = $request->get('rank');
        $parent_id = $request->get('parent_id');
        Entity::patchRanks($rank, $id, $parent_id, $user);
        return response()->json(null, 204);
    }

    // DELETE

    public function deleteEntity($id) {
        $user = auth()->user();
        if(!$user->can('delete_move_concepts')) {
            return response()->json([
                'error' => __('You do not have the permission to delete an entity')
            ], 403);
        }
        try {
            $entity = Entity::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist')
            ], 400);
        }
        $entity->delete();

        return response()->json(null, 204);
    }
}
