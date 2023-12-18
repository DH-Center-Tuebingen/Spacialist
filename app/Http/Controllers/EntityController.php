<?php

namespace App\Http\Controllers;

use App\Attribute;
use App\AttributeValue;
use App\Entity;
use App\EntityAttribute;
use App\EntityFile;
use App\EntityType;
use App\Exceptions\AmbiguousValueException;
use App\Exceptions\InvalidDataException;
use App\Geodata;
use App\Reference;
use App\ThConcept;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
        if(!$user->can('entity_read')) {
            return response()->json([
                'error' => __('You do not have the permission to get entities')
            ], 403);
        }
        $roots = Entity::getEntitiesByParent(null);

        return response()->json($roots);
    }

    public function getEntity($id) {
        $user = auth()->user();
        if(!$user->can('entity_read')) {
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

    public function getDataForEntityType(Request $request, $etid, $aid) {
        $user = auth()->user();
        if(!$user->can('entity_read') || !$user->can('entity_type_read') || !$user->can('entity_data_read')) {
            return response()->json([
                'error' => __('You do not have the permission to get an entity\'s data')
            ], 403);
        }
        try {
            $entityType = EntityType::findOrFail($etid);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity type does not exist')
            ], 400);
        }
        try {
            Attribute::findOrFail($aid);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This attribute does not exist')
            ], 400);
        }
        $constraints = $request->query();
        $entities = Entity::where('entity_type_id', $etid);
        foreach($constraints as $relation => $cons) {
            if($cons == 'has') {
                $entities->has($relation);
            } else if($cons == 'hasnot') {
                $entities->doesntHave($relation);
            }
        }
        $entities = $entities->get();
        $entityIds = $entities->pluck('id')->toArray();
        $values = AttributeValue::whereHas('attribute', function(Builder $q) {
                $q->where('datatype', '!=', 'sql');
            })
            ->whereIn('entity_id', $entityIds)
            ->where('attribute_id', $aid)
            ->get();
        $data = [];
        foreach($values as $value) {
            switch($value->attribute->datatype) {
                case 'entity':
                    $entity = Entity::find($value->entity_val)->name;
                    $value->name = $entity;
                    $value->value = $entity;
                    break;
                case 'entity-mc':
                    break;
                default:
                    $value->value = $value->getValue();
                    break;
            }
            $data[$value->entity_id] = $value;
        }

        $sqls = EntityAttribute::whereHas('attribute', function(Builder $q) {
                $q->where('datatype', 'sql');
            })
            ->where('entity_type_id', $etid)
            ->where('attribute_id', $aid)
            ->get();

        foreach($sqls as $sql) {
            // if entity_id is referenced several times
            // add an incrementing counter, so the
            // references are unique (required by PDO)
            $cnt = substr_count($sql->attribute->text, ':entity_id');
            if($cnt > 1) {
                $i = 0;
                $text = preg_replace_callback('/:entity_id/', function($matches) use (&$i) {
                    return $matches[0].'_'.$i++;
                }, $sql->attribute->text);
            } else {
                $text = $sql->attribute->text;
            }
            foreach($entityIds as $eid) {
                $safes = [];
                if($cnt > 1) {
                    for($i=0; $i<$cnt; $i++) {
                        $safes[':entity_id_'.$i] = $eid;
                    }
                } else {
                    $safes = [
                        ':entity_id' => $eid
                    ];
                }
                $sqlValue = DB::select($text, $safes);
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
                $data[$eid] = [
                    'value' => $sqlValue
                ];
            }
        }

        return response()->json($data);
    }

    public function getData($id, $aid = null) {
        $user = auth()->user();
        if(!$user->can('entity_read') || !$user->can('entity_data_read')) {
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
            try {
                Attribute::findOrFail($aid);
            } catch(ModelNotFoundException $e) {
                return response()->json([
                    'error' => __('This attribute does not exist')
                ], 400);
            }
            $attributes = AttributeValue::whereHas('attribute', function(Builder $q) {
                    $q->where('datatype', '!=', 'sql');
                })
                ->where('entity_id', $id)
                ->where('attribute_id', $aid)
                ->withModerated()
                ->get();
        } else {
            $attributes = AttributeValue::whereHas('attribute', function(Builder $q) {
                    $q->where('datatype', '!=', 'sql');
                })
                ->where('entity_id', $id)
                ->withModerated()
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

        $sqls = EntityAttribute::whereHas('attribute', function(Builder $q) {
                $q->where('datatype', 'sql');
            })
            ->where('entity_type_id', $entity->entity_type_id);
        if(isset($aid)) {
            $sqls->where('attribute_id', $aid);
        }
        $sqls = $sqls->get();

        foreach($sqls as $sql) {
            // if entity_id is referenced several times
            // add an incrementing counter, so the
            // references are unique (required by PDO)
            $cnt = substr_count($sql->attribute->text, ':entity_id');
            if($cnt > 1) {
                $safes = [];
                for($i=0; $i<$cnt; $i++) {
                    $safes[':entity_id_'.$i] = $id;
                }
                $i = 0;
                $text = preg_replace_callback('/:entity_id/', function($matches) use (&$i) {
                    return $matches[0].'_'.$i++;
                }, $sql->attribute->text);
            } else {
                $text = $sql->attribute->text;
                $safes = [
                    ':entity_id' => $id
                ];
            }
            $sqlValue = DB::select($text, $safes);
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

    public function getMetadata($id) {
        $user = auth()->user();
        if(!$user->can('entity_read') || !$user->can('entity_data_read')) {
            return response()->json([
                'error' => __('You do not have the permission to get an entity\'s metadata')
            ], 403);
        }

        try {
            $entity = Entity::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist')
            ], 400);
        }
        return response()->json([
            'creator' => $entity->creator,
            'editors' => $entity->editors,
            'history' => $entity->history,
        ]);
    }

    public function getParentIds($id) {
        $user = auth()->user();
        if(!$user->can('entity_read')) {
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

    public function getEntitiesByParent($id) {
        $user = auth()->user();
        if(!$user->can('entity_read')) {
            return response()->json([
                'error' => __('You do not have the permission to get an entity set')
            ], 403);
        }

        return Entity::getEntitiesByParent($id);
    }

    // POST

    public function addEntity(Request $request) {
        $user = auth()->user();
        if(!$user->can('entity_create')) {
            return response()->json([
                'error' => __('You do not have the permission to add a new entity')
            ], 403);
        }
        $this->validate($request, Entity::rules);

        $fields = $request->only(array_keys(Entity::rules));
        $etid = $request->get('entity_type_id');
        $reid = $request->get('root_entity_id');

        $res = Entity::create($fields, $etid, $user, $reid);

        if($res['type'] === 'entity') {
            return response()->json($res['entity'], 201);
        } else {
            return response()->json([
                'error' => $res['msg']
            ], $res['code']);
        }
    }

    public function duplicateEntity(Request $request, $id) {
        $user = auth()->user();
        if(!$user->can('entity_create')) {
            return response()->json([
                'error' => __('You do not have the permission to duplicate an entity')
            ], 403);
        }

        try {
            $entity = Entity::without(['user', 'parentIds', 'parentNames'])->findOrFail($id);
            unset($entity->comments_count);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist')
            ], 400);
        }

        $duplicate = $entity->replicate();
        $duplicate->created_at = Carbon::now();
        $duplicate->geodata_id = null;
        if(isset($duplicate->root_entity_id)) {
            $duplicate->rank = Entity::where('root_entity_id', $duplicate->root_entity_id)->max('rank') + 1;
        } else {
            $duplicate->rank = Entity::whereNull('root_entity_id')->max('rank') + 1;
        }
        $duplicate->user_id = $user->id;
        $duplicate->name = sp_copyname($duplicate->name);
        $duplicate->save();

        // Files, bibliographies, attribute_values
        $fileLinks = EntityFile::where('entity_id', $entity->id)->get();
        foreach($fileLinks as $fileLink) {
            $newLink = $fileLink->replicate();
            $newLink->entity_id = $duplicate->id;
            $newLink->user_id = $user->id;
            $newLink->save();
        }
        $refs = Reference::where('entity_id', $entity->id)->get();
        foreach($refs as $ref) {
            $newLink = $ref->replicate();
            $newLink->entity_id = $duplicate->id;
            $newLink->user_id = $user->id;
            $newLink->created_at = Carbon::now();
            $newLink->save();
        }
        $values = AttributeValue::where('entity_id', $entity->id)->get();
        foreach($values as $val) {
            unset($val->comments_count);
            $newValue = $val->replicate();
            $newValue->entity_id = $duplicate->id;
            $newValue->user_id = $user->id;
            $newValue->created_at = Carbon::now();
            $newValue->save();
        }

        return response()->json($duplicate, 201);
    }

    public function importData(Request $request) {
        $user = auth()->user();
        if(!$user->can('entity_create')) {
            return response()->json([
                'error' => __('You do not have the permission to import entity data')
            ], 403);
        }
        $this->validate($request, [
            'file' => 'required|file',
            'metadata' => 'required|json',
            'data' => 'required|json',
        ]);

        $file = $request->file('file');
        $metadata = json_decode($request->get('metadata'), true);
        $data = json_decode($request->get('data'), true);
        $handle = fopen($file->getRealPath(), 'r');

        $headerRow = null;
        $headerRead = false;
        $hasParent = false;
        $attributeIdx = [];
        $attributeTypes = [];
        $addedEntities = [];

        DB::beginTransaction();

        while(($row = fgetcsv($handle, 0, $metadata['delimiter'])) !== false) {
            if(!$headerRead) {
                $headerRead = true;
                $headerRow = $row;
                for($i = 0; $i<count($row); $i++) {
                    if($row[$i] == $data['name_column']) {
                        $nameIdx = $i;
                    } else if(isset($data['parent_column']) && $row[$i] == $data['parent_column']) {
                        $parentIdx = $i;
                        $hasParent = true;
                    } else {
                        foreach($data['attributes'] as $id => $a) {
                            if($a == $row[$i]) {
                                $attributeIdx[$id] = $i;
                                $attributeTypes[$id] = Attribute::find($id)->datatype;
                                break;
                            }
                        }
                    }
                }
                continue;
            }
            $rootEntityPath = $hasParent ? $row[$parentIdx] : null;

            try {
                $rootEntityId = Entity::getFromPath($rootEntityPath);
            } catch(AmbiguousValueException $ave) {
                DB::rollBack();
                return response()->json([
                    'error' => __($ave->getMessage()),
                    'data' => [
                        'count' => count($addedEntities) + 1,
                        'entry' => $row[$nameIdx],
                        'on' => $headerRow[$parentIdx],
                        'on_index' => $parentIdx + 1,
                        'on_value' => $row[$parentIdx],
                    ],
                ], 400);
            }

            $res = Entity::create([
                'name' => $row[$nameIdx],
            ], $data['entity_type_id'], $user, $rootEntityId);

            if($res['type'] === 'entity') {
                $addedEntities[] = $res['entity'];
                $eid = $res['entity']->id;
                foreach($attributeIdx as $key => $val) {
                    $aid = intval($key);
                    $type = $attributeTypes[$aid];
                    $attrVal = new AttributeValue();
                    $attrVal->entity_id = $eid;
                    $attrVal->attribute_id = $aid;
                    $attrVal->certainty = 100;
                    $attrVal->user_id = $user->id;
                    try {
                        $setValue = $attrVal->setValueFromRaw($row[$val], $type);
                        if($setValue === null) {
                            continue;
                        }
                        $attrVal->save();
                    } catch(InvalidDataException | AmbiguousValueException $e) {
                        DB::rollBack();
                        $colIdx = $val + 1;
                        return response()->json([
                            'error' => __($e->getMessage()),
                            'data' => [
                                'count' => count($addedEntities),
                                'entry' => $row[$nameIdx],
                                'on' => $headerRow[$val],
                                'on_index' => $colIdx,
                                'on_value' => $row[$val],
                            ],
                        ], 400);
                    }
                }
            } else {
                DB::rollBack();
                return response()->json([
                    'error' => $res['msg'],
                    'data' => [
                        'count' => count($addedEntities) + 1,
                        'entry' => $row[$nameIdx],
                        'on' => __('Create Entity from given data'),
                    ],
                ], $res['code']);
            }
        }
        fclose($handle);

        DB::commit();

        return response()->json($addedEntities, 201);
    }

    // PATCH

    public function patchAttributes($id, Request $request) {
        $user = auth()->user();
        if(!$user->can('entity_data_write')) {
            return response()->json([
                'error' => __('You do not have the permission to modify an entity\'s data')
            ], 403);
        }

        try {
            $entity = Entity::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist')
            ], 400);
        }

        foreach($request->request as $patch) {
            $op = $patch['op'];
            $aid = $patch['params']['aid'];
            switch($op) {
                case 'remove':
                    $attrval = AttributeValue::where([
                        ['entity_id', '=', $id],
                        ['attribute_id', '=', $aid]
                    ])->first();
                    if(!isset($attrval)) {
                        return response()->json([
                            'error' => __('This attribute value does either not exist or is in moderation state.')
                        ], 400);
                    }
                    if($user->isModerated()) {
                        $attrval->moderate('pending-delete', true);
                    } else {
                        $attrval->delete();
                    }
                    break;
                case 'add':
                    $alreadyAdded = AttributeValue::where('entity_id', $id)
                        ->where('attribute_id', $aid)
                        ->withModerated()
                        ->exists();
                    if($alreadyAdded) {
                        return response()->json([
                            'error' => __('There is already a value set for this attribute or it is in moderation state.')
                        ], 400);
                    }
                    $value = $patch['value'];
                    $attrval = new AttributeValue();
                    $attrval->entity_id = $id;
                    $attrval->attribute_id = $aid;
                    if($user->isModerated()) {
                        $attrval->moderate('pending', true, true);
                    }
                    break;
                case 'replace':
                     $alreadyModerated = AttributeValue::where('entity_id', $id)
                        ->where('attribute_id', $aid)
                        ->onlyModerated()
                        ->exists();
                    if($alreadyModerated) {
                        return response()->json([
                            'error' => __('This attribute value is in moderation state. A user with appropriate permissions has to accept or deny it first.')
                        ], 400);
                    }
                    $value = $patch['value'];
                    $attrval = AttributeValue::where([
                        ['entity_id', '=', $id],
                        ['attribute_id', '=', $aid]
                    ])->first();
                    if($user->isModerated()) {
                        $attrval = $attrval->moderate('pending', false, true);
                        unset($attrval->comments_count);
                    }
                    break;
                default:
                    return response()->json([
                        'error' => __('Unknown operation')
                    ], 400);
            }

            // no further action required for deleted attribute values, continue with next patch
            if($op == 'remove') continue;
            
            $attr = Attribute::find($aid);
            try {
                $formKeyValue = AttributeValue::getFormattedKeyValue($attr->datatype, $value);
            } catch(InvalidDataException $ide) {
                return response()->json([
                    'error' => $ide->getMessage(),
                ], 422);
            }
            $attrval->{$formKeyValue->key} = $formKeyValue->val;
            $attrval->user_id = $user->id;
            $attrval->save();
        }

        // Save model if last editor changed
        // Only update timestamps otherwise
        $entity->user_id = $user->id;
        if($entity->isDirty()) {
            $entity->save();
        } else {
            $entity->touch();
        }

        $entity->load('user');

        return response()->json($entity);
    }

    public function patchAttribute($id, $aid, Request $request) {
        $user = auth()->user();
        if(!$user->can('entity_data_write')) {
            return response()->json([
                'error' => __('You do not have the permission to modify an entity\'s data')
            ], 403);
        }
        $this->validate($request, AttributeValue::patchRules);

        try {
            Entity::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist')
            ], 400);
        }
        try {
            Attribute::findOrFail($aid);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This attribute does not exist')
            ], 400);
        }

        $attrValue = AttributeValue::firstOrCreate([
            'entity_id' => $id,
            'attribute_id' => $aid,
        ], [
            'user_id' => $user->id
        ]);
        // When attribute value already exists and nothing changed
        // (same certainty)
        if(
            !$attrValue->wasRecentlyCreated
            &&
            ($request->has('certainty') && $request->get('certainty') == $attrValue->certainty)
        ) {
            return response()->json($attrValue);
        }
        $attrValue->user_id = $user->id;
        $values = $request->only(array_keys(AttributeValue::patchRules));
        $attrValue->patch($values);

        return response()->json($attrValue, 201);
    }

    public function multieditAttributes(Request $request) {
        $user = auth()->user();
        if(!$user->can('entity_data_write')) {
            return response()->json([
                'error' => __('You do not have the permission to modify an entity\'s data')
            ], 403);
        }

        $this->validate($request, [
            'entity_ids' => 'required|array',
            'entries' => 'required|array',
        ]);

        $entities = $request->get('entity_ids');
        $attrValues = $request->get('entries');

        DB::beginTransaction();

        foreach($attrValues as $av) {
            try {
                $attr = Attribute::findOrFail($av['attribute_id']);
            } catch(ModelNotFoundException $e) {
                DB::rollBack();
                return response()->json([
                    'error' => __('This attribute does not exist'),
                ], 400);
            }
            try {
                $formKeyValue = AttributeValue::getFormattedKeyValue($attr->datatype, $av['value']);
            } catch(InvalidDataException $ide) {
                DB::rollBack();
                return response()->json([
                    'error' => $ide->getMessage(),
                ], 422);
            }
            foreach($entities as $eid) {
                AttributeValue::updateOrCreate(
                    ['entity_id' => $eid, 'attribute_id' => $av['attribute_id']],
                    [
                        $formKeyValue->key => $formKeyValue->val,
                        'user_id' => $user->id,
                    ]
                );
            }
        }

        DB::commit();

        return response()->json(null, 204);
    }

    public function handleModeration($id, $aid, Request $request) {
        $user = auth()->user();
        if(!$user->can('entity_data_write') || $user->isModerated()) {
            return response()->json([
                'error' => __('You do not have the permission to modify an entity\'s data')
            ], 403);
        }
        $this->validate($request, [
            'action' => 'required|string|mod_action',
            'value' => 'nullable',
        ]);

        $action = $request->get('action');

        try {
            Entity::findOrFail($id);
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

        $attrValue = AttributeValue::where('entity_id', $id)
            ->where('attribute_id', $aid)
            ->onlyModerated()
            ->first();

        if(!isset($attrValue)) {
            return response()->json([
                'error' => __('This attribute value does not exist')
            ], 400);
        }

        $attrValue->moderate($action);

        $editedValue = $request->get('value');
        if(isset($editedValue) && $action == 'accept') {
            try {
                $formKeyValue = AttributeValue::getFormattedKeyValue($attribute->datatype, $editedValue);
            } catch(InvalidDataException $ide) {
                return response()->json([
                    'error' => $ide->getMessage(),
                ], 422);
            }
            $attrValue->{$formKeyValue->key} = $formKeyValue->val;
            $attrValue->user_id = $user->id;
            $attrValue->save();
        }

        return response()->json(null, 204);
    }

    public function patchName($id, Request $request) {
        $user = auth()->user();
        if(!$user->can('entity_write')) {
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
        $entity->user_id = $user->id;

        $entity->save();
        
        $entity->load('user');

        return response()->json($entity);
    }

    public function patchMetadata($id, Request $request) {
        $user = auth()->user();
        if(!$user->can('entity_write')) {
            return response()->json([
                'error' => __('You do not have the permission to modify an entity\'s metadata')
            ], 403);
        }
        $this->validate($request, [
            'licence' => 'nullable|string'
        ]);

        try {
            $entity = Entity::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist')
            ], 400);
        }

        $licence = $request->get('licence');
        $metadata = $entity->metadata;
        $metadata['licence'] = $licence;
        $entity->metadata = $metadata;
        $entity->save();
        return response()->json($entity->metadata);
    }

    public function moveEntity(Request $request, $id) {
        $user = auth()->user();
        if(!$user->can('entity_write')) {
            return response()->json([
                'error' => __('You do not have the permission to modify an entity')
            ], 403);
        }
        $this->validate($request, [
            'rank' => 'required|integer',
            'parent_id' => 'nullable|integer|exists:entities,id',
            'to_end' => 'boolean',
        ]);

        try {
            $entity = Entity::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist')
            ], 400);
        }

        $rank = $request->get('rank');
        $parent_id = $request->get('parent_id');
        $addToEnd = $request->get('to_end');

        if($addToEnd) {
            if(isset($parent_id)) {
                $rank = Entity::where('root_entity_id', $parent_id)->max('rank') + 1;
            } else {
                $rank = Entity::whereNull('root_entity_id')->max('rank') + 1;
            }
        }

        Entity::patchRanks($rank, $id, $parent_id, $user);
        return response()->json(null, 204);
    }

    // DELETE

    public function deleteEntity($id) {
        $user = auth()->user();
        if(!$user->can('entity_delete')) {
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
