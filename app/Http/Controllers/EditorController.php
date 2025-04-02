<?php

namespace App\Http\Controllers;

// use App\AvailableLayer;
use App\Attribute;
use App\AttributeValue;
use App\AvailableLayer;
use App\Entity;
use App\EntityAttribute;
use App\EntityType;
use App\EntityTypeRelation;
use \App\Plugins\Map\App\Geodata;
use App\Plugin;
use App\ThConcept;
use App\AttributeTypes\AttributeBase;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EditorController extends Controller {
    // GET

    public function getEntityTypeOccurrenceCount($id) {
        $user = auth()->user();
        if(!$user->can('entity_read')) {
            return response()->json([
                'error' => __('You do not have the permission to get an entity type\'s occurrences')
            ], 403);
        }
        $cnt = Entity::where('entity_type_id', $id)->count();
        return response()->json($cnt);
    }

    public function getAttributeValueOccurrenceCount($aid, $ctid = -1) {
        $user = auth()->user();
        if(!$user->can('entity_data_read')) {
            return response()->json([
                'error' => __('You do not have the permission to get an attribute value\'s occurrences')
            ], 403);
        }
        $query = AttributeValue::where('attribute_id', $aid);
        if($ctid > -1) {
            $query->where('entity_type_id', $ctid);
            $query->join('entities', 'entities.id', '=', 'entity_id');
        }
        $cnt = $query->count();
        return response()->json($cnt);
    }

    public function getEntityType($id) {
        $user = auth()->user();
        if(!$user->can('entity_type_read')) {
            return response()->json([
                'error' => __('You do not have the permission to get an entity type\'s data')
            ], 403);
        }
        try {
            $entityType = EntityType::with('sub_entity_types')->findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity-type does not exist')
            ], 400);
        }
        return response()->json($entityType);
    }

    public function getTopEntityTypes() {
        $user = auth()->user();
        if(!$user->can('entity_type_read')) {
            return response()->json([
                'error' => __('You do not have the permission to view entity data')
            ], 403);
        }
        $entityTypes = EntityType::where('is_root', true)->get();
        return response()->json($entityTypes);
    }

    public function getAttributes() {
        $user = auth()->user();
        if(!$user->can('attribute_read')) {
            return response()->json([
                'error' => __('You do not have the permission to view entity data')
            ], 403);
        }
        $attributes = Attribute::whereNull('parent_id')->withCount('entity_types')->orderBy('id')->get();
        $selections = [];
        foreach($attributes as $a) {
            $selection = $a->getSelection();
            if(isset($selection)) {
                // Workaround to check if it is a plain array or a assoc array (table columns)
                // if assoc array, add each entry to their corresponding id
                if(!isset($selection[0])) {
                    foreach($selection as $id => $sel) {
                        $selections[$id] = $sel;
                    }
                } else {
                    $selections[$a->id] = $selection;
                }
            }

            if($a->datatype == 'table') {
                $a->columns = Attribute::where('parent_id', $a->id)->get()->keyBy('id');
            }
        }
        return response()->json([
            'attributes' => $attributes,
            'selections' => $selections,
        ]);
    }

    public function getAttributeTypes() {
        $user = auth()->user();
        if(!$user->can('attribute_read')) {
            return response()->json([
                'error' => __('You do not have the permission to view available attribute types')
            ], 403);
        }

        return response()->json(AttributeBase::getTypes(true));
    }

    public function getAvailableGeometryTypes() {
        if(Plugin::isInstalled('Map')) {
            $types = Geodata::getAvailableGeometryTypes();
            return response()->json($types);
        } else {
            return response()->json();
        }
    }

    // POST

    public function addEntityType(Request $request) {
        $user = auth()->user();
        if(!$user->can('entity_type_create')) {
            return response()->json([
                'error' => __('You do not have the permission to create a new entity type')
            ], 403);
        }
        $this->validate($request, [
            'concept_url' => 'required|exists:th_concept',
            'is_root' => 'required|boolean_string',
            'geometry_type' => 'required|geometry'
        ]);

        $curl = $request->get('concept_url');
        $is_root = sp_parse_boolean($request->get('is_root'));
        $entityType = new EntityType();
        $entityType->thesaurus_url = $curl;
        $entityType->is_root = $is_root;
        $entityType->color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
        $entityType->save();
        $entityType = EntityType::find($entityType->id);

        // TODO:: Reimplement in plugin [SO]
        //
        // $geomtype = $request->get('geometry_type');
        // AvailableLayer::createFromArray([
        //     'name' => '',
        //     'url' => '',
        //     'type' => $geomtype,
        //     'opacity' => 1,
        //     'visible' => true,
        //     'is_overlay' => true,
        //     'entity_type_id' => $cType->id
        // ]);

        // $cType->load('layer');

        return response()->json($entityType, 201);
    }

    public function addAttribute(Request $request) {
        $user = auth()->user();
        if(!$user->can('attribute_create')) {
            return response()->json([
                'error' => __('You do not have the permission to add attributes')
            ], 403);
        }
        $this->validate($request, [
            'label_id' => 'required|integer|exists:th_concept,id',
            'datatype' => 'required|string',
            'root_id' => 'nullable|integer|exists:th_concept,id',
            'root_attribute_id' => 'nullable|integer|exists:attributes,id',
            'restricted_types' => 'nullable|array|exists:entity_types,id',
            'columns' => 'required_if:datatype,table|array|min:1',
            'text' => 'string',
            'recursive' => 'nullable|boolean_string',
            'si_base' => 'nullable|si_baseunit',
            'si_default' => 'nullable|si_unit:si_base'
        ]);

        $lid = $request->get('label_id');
        $datatype = $request->get('datatype');
        $curl = ThConcept::find($lid)->concept_url;
        $attr = new Attribute();
        $attr->thesaurus_url = $curl;
        $attr->datatype = $datatype;
        $attr->recursive = $request->has('recursive') && sp_parse_boolean($request->get('recursive'));
        if($request->has('root_id')) {
            $pid = $request->get('root_id');
            $purl = ThConcept::find($pid)->concept_url;
            $attr->thesaurus_root_url = $purl;
        } else if($request->has('root_attribute_id')) {
            $frid = $request->get('root_attribute_id');
            $attr->root_attribute_id = $frid;
        }
        if($request->has('restricted_types')) {
            $attr->restrictions = $request->get('restricted_types');
        }
        if($request->has('text')) {
            $attr->text = $request->get('text');
        }
        if($request->has('si_base')) {
            $attr->metadata = [
                'si_baseunit' => $request->get('si_base'),
                'si_default' => $request->get('si_default'),
            ];
        }
        $attr->save();

        if($datatype == 'table') {
            $cols = $request->input('columns');
            foreach($cols as $col) {
                if(!isset($col['label_id']) && !isset($col['datatype'])) continue;
                $curl = ThConcept::find($col['label_id'])->concept_url;
                $childAttr = new Attribute();
                $childAttr->thesaurus_url = $curl;
                $childAttr->datatype = $col['datatype'];
                $childAttr->recursive = sp_parse_boolean($col['recursive']);
                if(isset($col['root_id'])) {
                    $pid = $col['root_id'];
                    $purl = ThConcept::find($pid)->concept_url;
                    $childAttr->thesaurus_root_url = $purl;
                }
                if(isset($col['si_base'])) {
                    $childAttr->metadata = [
                        'si_baseunit' => $col['si_base'],
                        'si_default' => $col['si_default'],
                    ];
                }
                if(array_key_exists('restricted_types', $col)) {
                    $childAttr->restrictions = $col['restricted_types'];
                }
                $childAttr->parent_id = $attr->id;
                $childAttr->save();
            }
        }
        $attr = Attribute::find($attr->id);
        $attr->columns = Attribute::where('parent_id', $attr->id)->get();
        $sel = $attr->getSelection();

        $ret = [
            'attribute' => $attr,
        ];
        if(isset($sel)) {
            $ret['selection'] = $sel;
        }
        return response()->json($ret, 201);
    }

    public function addAttributeToEntityType(Request $request, $etid) {
        $user = auth()->user();
        if(!$user->can('attribute_write') || !$user->can('entity_type_write')) {
            return response()->json([
                'error' => __('You do not have the permission to add attributes to an entity type')
            ], 403);
        }
        $this->validate($request, [
            'attribute_id' => 'required|integer|exists:attributes,id',
            'position' => 'integer'
        ]);

        try {
            $entityType = EntityType::findOrFail($etid);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity-type does not exist')
            ], 400);
        }

        $aid = $request->get('attribute_id');
        $alreadyAdded = EntityAttribute::where('entity_type_id', $etid)
            ->where('attribute_id', $aid)
            ->whereHas('attribute', function(Builder $query) {
                $query->where('multiple', false);
            })
            ->exists();
        if($alreadyAdded) {
            return response()->json([
                'error' => __('This attribute is already added to this entity-type')
            ], 400);
        }

        $pos = $request->get('position');
        if(!isset($pos)) {
            $attrsCnt = EntityAttribute::where('entity_type_id', '=', $etid)->count();
            $pos = $attrsCnt + 1; // add new attribute to the end
        } else {
            $successors = EntityAttribute::where('entity_type_id', $etid)
                ->where('position', '>=', $pos)
                ->get();
            foreach($successors as $s) {
                $s->position++;
                $s->save();
            }
        }
        $entityAttr = new EntityAttribute();
        $entityAttr->entity_type_id = $etid;
        $entityAttr->attribute_id = $aid;
        $entityAttr->position = $pos;
        $entityAttr->save();
        $entityAttr = EntityAttribute::find($entityAttr->id);

        $attr = Attribute::find($aid);
        AttributeBase::onAddHandler($attr, $entityType, $user);

        $entityAttr->load('attribute');
        return response()->json($entityAttr, 201);
    }

    public function duplicateEntityType(Request $request, $ctid) {
        $user = auth()->user();
        if(!$user->can('entity_type_create')) {
            return response()->json([
                'error' => __('You do not have the permission to duplicate an entity type')
            ], 403);
        }

        try {
            $entityType = EntityType::findOrFail($ctid);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity-type does not exist')
            ], 400);
        }

        $duplicate = $entityType->replicate();
        $duplicate->save();

        // TODO:: This should be handled by a hook in the plugin [SO]
        // if($entityType->layer) {
        //     $newLayer = $entityType->layer->replicate();
        //     $newLayer->entity_type_id = $duplicate->id;
        //     $newLayer->position = AvailableLayer::where('is_overlay', true)->max('position') + 1;
        //     $newLayer->save();
        // }

        foreach($entityType->attributes as $attribute) {
            $newAttribute = EntityAttribute::where('attribute_id', $attribute->pivot->attribute_id)
                ->where('entity_type_id', $attribute->pivot->entity_type_id)
                ->first()
                ->replicate();
            $newAttribute->entity_type_id = $duplicate->id;
            $newAttribute->save();
        }

        $relations = EntityTypeRelation::where('parent_id', $ctid)
            ->orWhere('child_id', $ctid)
            ->get();
        foreach($relations as $rel) {
            $newSet = $rel
                ->replicate();
            if($newSet->parent_id == $ctid) {
                $newSet->parent_id = $duplicate->id;
            }
            if($newSet->child_id == $ctid) {
                $newSet->child_id = $duplicate->id;
            }
            $newSet->save();
        }

        $duplicate->load('sub_entity_types');
        // TODO handle in Map Plugin
        // $duplicate->load('layer');

        return response()->json($duplicate);
    }

    // PATCH

    public function patchEntityType(Request $request, $etid) {
        $user = auth()->user();
        if(!$user->can('entity_type_write')) {
            return response()->json([
                'error' => __('You do not have the permission to modify entity-type')
            ], 403);
        }
        $this->validate($request, [
            'data' => 'required|array',
        ]);

        try {
            $entityType = EntityType::findOrFail($etid);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity-type does not exist')
            ], 400);
        }
        $relationData = Arr::only(
            $request->get('data'),
            ['is_root', 'sub_entity_types', 'color'],
        );
        $propData = Arr::only(
            $request->get('data'),
            array_keys(EntityType::patchRules),
        );

        $updateRelation = count($relationData) > 0;
        $updateProps = count($propData) > 0;

        if(!$updateRelation && !$updateProps) {
            return response()->json([
                'error' => __('The given data is invalid')
            ], 400);
        }

        if($updateRelation) {
            $entityType->setRelationInfo($relationData);
        }

        if($updateProps) {
            foreach($propData as $key => $prop) {
                $entityType->{$key} = $prop;
            }
        }
        $entityType->save();

        return response()->json($entityType, 200);
    }

    public function reorderAttribute(Request $request, $ctid, $aid) {
        $user = auth()->user();
        if(!$user->can('entity_type_write')) {
            return response()->json([
                'error' => __('You do not have the permission to reorder attributes')
            ], 403);
        }
        $this->validate($request, [
            'position' => 'required|integer|exists:entity_attributes,position'
        ]);

        $pos = $request->get('position');
        $ca = EntityAttribute::where([
            ['attribute_id', '=', $aid],
            ['entity_type_id', '=', $ctid]
        ])->first();

        if($ca === null) {
            return response()->json([
                'error' => __('Entity Attribute not found')
            ], 400);
        }

        // Same position, nothing to do
        if($ca->position == $pos) {
            return response()->json(null, 204);
        }
        if($ca->position < $pos) {
            $successors = EntityAttribute::where([
                ['position', '>', $ca->position],
                ['position', '<=', $pos],
                ['entity_type_id', '=', $ctid]
            ])->get();
            foreach($successors as $s) {
                $s->position--;
                $s->save();
            }
        } else { // $ca->position > $pos
            $predecessors = EntityAttribute::where([
                ['position', '<', $ca->position],
                ['position', '>=', $pos],
                ['entity_type_id', '=', $ctid]
            ])->get();
            foreach($predecessors as $p) {
                $p->position++;
                $p->save();
            }
        }
        $ca->position = $pos;
        $ca->save();
        return response()->json(null, 204);
    }

    public function patchDependency(Request $request, $etid, $aid) {
        $user = auth()->user();
        if(!$user->can('entity_type_write')) {
            return response()->json([
                'error' => __('You do not have the permission to add/modify attribute dependencies')
            ], 403);
        }
        $this->validate($request, [
            'data' => 'required|array',
        ]);

        $entityAttribute = EntityAttribute::where([
            ['attribute_id', '=', $aid],
            ['entity_type_id', '=', $etid]
        ])->first();

        if($entityAttribute === null) {
            return response()->json([
                'error' => __('Entity Attribute not found')
            ], 400);
        }

        $dependencyData = $request->get('data');
        $hasData = false;
        $dependsOn = [
            'or' => $dependencyData['or'],
        ];
        $operators = [
            '<' => true,
            '>' => true,
            '<=' => true,
            '>=' => true,
            '=' => true,
            '!=' => true,
            '?' => false,
            '!?' => false,
        ];
        foreach($dependencyData['groups'] as $group) {
            if(count($group['rules']) > 0) {
                $hasData = true;
                $groupRules = [];
                foreach($group['rules'] as $rule) {
                    if(!in_array($rule['operator'], array_keys($operators))) {
                        return response()->json([
                            'error' => __('Operator mismatch')
                        ], 400);
                    }
                    if(!EntityAttribute::where('attribute_id', $rule['attribute'])->exists()) {
                        return response()->json([
                            'error' => __('Entity attribute does not exist')
                        ], 400);
                    }

                    $formattedRule = [
                        'operator' => $rule['operator'],
                        'on' => $rule['attribute'],
                    ];
                    if($operators[$rule['operator']]) {
                        $formattedRule['value'] = $rule['value'];
                    }
                    $groupRules[] = $formattedRule;
                }
                $dependsOn['groups'][] = [
                    'or' => $group['or'],
                    'rules' => $groupRules,
                ];
            }
        }

        if(!$hasData) {
            $dependsOn = null;
        }

        $entityAttribute->depends_on = $dependsOn;
        $entityAttribute->save();
        return response()->json($entityAttribute->depends_on, 200);
    }

    public function patchAttributeMetadata(Request $request, $id) {
        $user = auth()->user();
        if(!$user->can('entity_type_write')) {
            return response()->json([
                'error' => __('You do not have the permission to edit attribute names')
            ], 403);
        }
        $this->validate($request, [
            'title' => 'string|required_without:width',
            'width' => 'integer|required_without:title',
        ]);

        try {
            $entityAttribute = EntityAttribute::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('Entity Attribute not found')
            ], 400);
        }

        $metadata = json_decode($entityAttribute->metadata) ?? new \stdClass();

        if($request->has('title')) {
            $title = $request->get('title');
            $metadata->title = $title;
        }
        if($request->has('width')) {
            $width = $request->get('width');
            $metadata->width = $width;
        }

        $entityAttribute->metadata = json_encode($metadata);
        $entityAttribute->save();

        return response()->json($metadata, 200);
    }

    // DELETE

    public function deleteEntityType($id) {
        $user = auth()->user();
        if(!$user->can('entity_type_delete')) {
            return response()->json([
                'error' => __('You do not have the permission to delete entity types')
            ], 403);
        }

        try {
            $entityType = EntityType::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity-type does not exist')
            ], 400);
        }

        $entityType->delete();
        return response()->json(null, 204);
    }

    public function deleteAttribute($id) {
        $user = auth()->user();
        if(!$user->can('attribute_delete')) {
            return response()->json([
                'error' => __('You do not have the permission to delete attributes')
            ], 403);
        }

        try {
            $attribute = Attribute::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This attribute does not exist')
            ], 400);
        }

        $entityAttributes = EntityAttribute::where('attribute_id', $id)->get();
        foreach($entityAttributes as $ea) {
            $ea->removeFromEntityType();
            $ea->delete();
        }

        $attribute->delete();
        return response()->json(null, 204);
    }

    public function removeAttributeFromEntityType($id) {
        $user = auth()->user();
        if(!$user->can('entity_type_write')) {
            return response()->json([
                'error' => __('You do not have the permission to remove attributes from entity types')
            ], 403);
        }
        try {
            $ea = EntityAttribute::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('Entity Attribute not found')
            ], 400);
        }

        $ea->removeFromEntityType();
        $ea->delete();

        return response()->json(null, 204);
    }
}
