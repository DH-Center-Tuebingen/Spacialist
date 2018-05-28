<?php

namespace App\Http\Controllers;

// use App\AvailableLayer;
use \DB;
use App\Attribute;
use App\AttributeValue;
use App\Context;
use App\ContextAttribute;
use App\ContextType;
use App\ContextTypeRelation;
use App\Helpers;
use App\ThConcept;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EditorController extends Controller {
    // GET

    public function getContextTypeOccurrenceCount($id) {
        $cnt = Context::where('context_type_id', $id)->count();
        return response()->json($cnt);
    }

    public function getAttributeOccurrenceCount($aid, $ctid = -1) {
        $query = AttributeValue::where('attribute_id', $aid);
        if($ctid > -1) {
            $query->where('context_type_id', $ctid);
            $query->join('contexts', 'contexts.id', '=', 'context_id');
        }
        $cnt = $query->count();
        return response()->json($cnt);
    }

    public function getContextType($id) {
        try {
            $contextType = ContextType::with('sub_context_types')->findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This context-type does not exist'
            ], 400);
        }
        return response()->json($contextType);
    }

    public function getContextTypeAttributes($id) {
        $attributes = DB::table('context_types as c')
            ->where('c.id', $id)
            ->whereNull('a.parent_id')
            ->join('context_attributes as ca', 'c.id', '=', 'ca.context_type_id')
            ->join('attributes as a', 'ca.attribute_id', '=', 'a.id')
            ->orderBy('ca.position', 'asc')
            ->get();
        $selections = [];
        $dependencies = [];
        foreach($attributes as $a) {
            if(isset($a->depends_on)) {
                $dependsOn = json_decode($a->depends_on);
                foreach($dependsOn as $depAttr => $dep) {
                    if(!isset($dependencies[$depAttr])) {
                        $dependencies[$depAttr] = [];
                    }
                    $dependencies[$depAttr][] = $dep;
                }
            }
            unset($a->depends_on);
            switch($a->datatype) {
                case 'string-sc':
                case 'string-mc':
                case 'epoch':
                    $selections[$a->id] = ThConcept::getChildren($a->thesaurus_root_url);
                    break;
                case 'table':
                    $a->columns = Attribute::where('parent_id', $a->id)->get()->keyBy('id');
                    // Only string-sc is allowed in tables
                    $columns = Attribute::where('parent_id', $a->id)
                        ->where('datatype', 'string-sc')
                        ->get();
                    foreach($columns as $c) {
                        $selections[$c->id] = ThConcept::getChildren($c->thesaurus_root_url);
                    }
                    break;
                default:
                    break;
            }
        }
        return response()->json([
            'attributes' => $attributes,
            'selections' => $selections,
            'dependencies' => $dependencies
        ]);
    }

    public function getAttributeSelection($id) {
        try {
            $attribute = Attribute::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This attribute does not exist'
            ], 400);
        }

        $attribute->columns = Attribute::where('parent_id', $attribute->id)->get();
        $selection = [];
        switch($attribute->datatype) {
            case 'string-sc':
            case 'string-mc':
            case 'epoch':
                $selection = ThConcept::getChildren($attribute->thesaurus_root_url);
                break;
            case 'table':
                // Only string-sc is allowed in tables
                $columns = Attribute::where('parent_id', $attribute->id)
                    ->where('datatype', 'string-sc')
                    ->get();
                foreach($columns as $c) {
                    $selection = ThConcept::getChildren($c->thesaurus_root_url);
                }
                break;
            default:
                break;
        }

        return response()->json(array_values($selection));
    }

    public function getTopContextTypes() {
        $contextTypes = ContextType::where('is_root', true)->get();
        return response()->json($contextTypes);
    }

    public function getContextTypesByParent($cid) {
        try {
            $parentContext = Context::findOrFail($cid);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This context-type does not exist'
            ], 400);
        }
        $id = $parentContext->context_type_id;
        $relations = ContextTypeRelation::where('parent_id', $id)->pluck('child_id')->toArray();
        $contextTypes = ContextType::find($relations);
        return response()->json($contextTypes);
    }

    public function getAttributeTypes() {
        return response()->json([
            [
                'datatype' => 'string',
                'description' => 'attribute.string.desc'
            ],
            [
                'datatype' => 'stringf',
                'description' => 'attribute.stringf.desc'
            ],
            [
                'datatype' => 'double',
                'description' => 'attribute.double.desc'
            ],
            [
                'datatype' => 'string-sc',
                'description' => 'attribute.string-sc.desc'
            ],
            [
                'datatype' => 'string-mc',
                'description' => 'attribute.string-mc.desc'
            ],
            [
                'datatype' => 'epoch',
                'description' => 'attribute.epoch.desc'
            ],
            [
                'datatype' => 'date',
                'description' => 'attribute.date.desc'
            ],
            [
                'datatype' => 'dimension',
                'description' => 'attribute.dimension.desc'
            ],
            [
                'datatype' => 'list',
                'description' => 'attribute.list.desc'
            ],
            [
                'datatype' => 'geography',
                'description' => 'attribute.geography.desc'
            ],
            [
                'datatype' => 'integer',
                'description' => 'attribute.integer.desc'
            ],
            [
                'datatype' => 'boolean',
                'description' => 'attribute.boolean.desc'
            ],
            [
                'datatype' => 'percentage',
                'description' => 'attribute.percentage.desc'
            ],
            [
                'datatype' => 'context',
                'description' => 'attribute.context.desc'
            ],
            [
                'datatype' => 'table',
                'description' => 'attribute.table.desc'
            ],
            [
                'datatype' => 'sql',
                'description' => 'attribute.sql.desc'
            ]
        ]);
    }

    // POST

    public function addContextType(Request $request) {
        $this->validate($request, [
            'concept_url' => 'required|url|exists:th_concept',
            'is_root' => 'required|boolean_string'
            // 'geomtype' => 'required|geom_type'
        ]);

        $curl = $request->get('concept_url');
        $is_root = Helpers::parseBoolean($request->get('is_root'));
        // $geomtype = $request->get('geomtype');
        $cType = new ContextType();
        $cType->thesaurus_url = $curl;
        $cType->is_root = $is_root;
        $cType->save();

        // $layer = new AvailableLayer();
        // $layer->name = '';
        // $layer->url = '';
        // $layer->type = $geomtype;
        // $layer->opacity = 1;
        // $layer->visible = true;
        // $layer->is_overlay = true;
        // $layer->position = AvailableLayer::where('is_overlay', '=', true)->max('position') + 1;
        // $layer->context_type_id = $cType->id;
        // $layer->color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
        // $layer->save();

        return response()->json($cType, 201);
    }

    public function setRelationInfo(Request $request, $id) {
        $this->validate($request, [
            'is_root' => 'boolean_string',
            'sub_context_types' => 'array'
        ]);
        try {
            $contextType = ContextType::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This context-type does not exist'
            ], 400);
        }
        $is_root = $request->get('is_root');
        $subs = $request->get('sub_context_types');
        $contextType->setRelationInfo($is_root, $subs);
        return response()->json(null, 204);
    }

    public function addAttribute(Request $request) {
        $this->validate($request, [
            'label_id' => 'required|integer|exists:th_concept,id',
            'datatype' => 'required|string',
            'root_id' => 'nullable|integer|exists:th_concept,id',
            'columns' => 'nullable|json',
            'text' => 'string'
        ]);

        $lid = $request->get('label_id');
        $datatype = $request->get('datatype');
        $curl = ThConcept::find($lid)->concept_url;
        $attr = new Attribute();
        $attr->thesaurus_url = $curl;
        $attr->datatype = $datatype;
        if($request->has('root_id')) {
            $pid = $request->get('root_id');
            $purl = ThConcept::find($pid)->concept_url;
            $attr->thesaurus_root_url = $purl;
        }
        if($request->has('text')) {
            $attr->text = $request->get('text');
        }
        $attr->save();

        if($datatype == 'table') {
            $cols = json_decode($request->get('columns'));
            foreach($cols as $col) {
                if(!isset($col->label_id) && !isset($col->datatype)) continue;
                $curl = ThConcept::find($col->label_id)->concept_url;
                $childAttr = new Attribute();
                $childAttr->thesaurus_url = $curl;
                $childAttr->datatype = $col->datatype;
                if(isset($col->root_id)) {
                    $pid = $col->root_id;
                    $purl = ThConcept::find($pid)->concept_url;
                    $childAttr->thesaurus_root_url = $purl;
                }
                $childAttr->root_id = $attr->id;
                $childAttr->save();
            }
        }

        return response()->json($attr, 201);
    }

    public function addAttributeToContextType(Request $request, $ctid) {
        $this->validate($request, [
            'attribute_id' => 'required|integer|exists:attributes,id',
            'position' => 'integer'
        ]);

        $aid = $request->get('attribute_id');
        $pos = $request->get('position');
        if(!isset($pos)) {
            $attrsCnt = ContextAttribute::where('context_type_id', '=', $ctid)->count();
            $pos = $attrsCnt + 1; // add new attribute to the end
        } else {
            $successors = ContextAttribute::where('context_type_id', $ctid)
                ->where('position', '>=', $pos)
                ->get();
            foreach($successors as $s) {
                $s->position++;
                $s->save();
            }
        }
        $ca = new ContextAttribute();
        $ca->context_type_id = $ctid;
        $ca->attribute_id = $aid;
        $ca->position = $pos;
        $ca->save();

        $a = Attribute::find($aid);
        $ca->datatype = $a->datatype;

        return response()->json(DB::table('context_types as c')
                ->where('ca.id', $ca->id)
                ->join('context_attributes as ca', 'c.id', '=', 'ca.context_type_id')
                ->join('attributes as a', 'ca.attribute_id', '=', 'a.id')
                ->first(), 201);
    }

    // PATCH

    public function reorderAttribute(Request $request, $ctid, $aid) {
        $this->validate($request, [
            'position' => 'required|integer|exists:context_attributes,position'
        ]);

        $pos = $request->get('position');
        $ca = ContextAttribute::where([
            ['attribute_id', '=', $aid],
            ['context_type_id', '=', $ctid]
        ])->first();

        if($ca === null){
            return response()->json([
                'error' => 'No ContextAttribute found'
            ], 400);
        }

        // Same position, nothing to do
        if($ca->position == $pos) {
            return response()->json();
        }
        if($ca->position < $pos) {
            $successors = ContextAttribute::where([
                ['position', '>', $ca->position],
                ['position', '<=', $pos],
                ['context_type_id', '=', $ctid]
            ])->get();
            foreach($successors as $s) {
                $s->position--;
                $s->save();
            }
        } else { // $ca->position > $pos
            $predecessors = ContextAttribute::where([
                ['position', '<', $ca->position],
                ['position', '>=', $pos],
                ['context_type_id', '=', $ctid]
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

    public function patchDependency(Request $request, $ctid, $aid) {
        $this->validate($request, [
            'd_attribute' => 'required|nullable|integer|exists:context_attributes,attribute_id',
            'd_operator' => 'required|nullable|in:<,>,=',
            'd_value' => 'required|nullable'
        ]);

        $contextAttribute = ContextAttribute::where([
            ['attribute_id', '=', $aid],
            ['context_type_id', '=', $ctid]
        ])->first();

        if($contextAttribute === null){
            return response()->json([
                'error' => 'Context Attribute not found'
            ], 400);
        }

        $dAttribute = $request->get('d_attribute');
        $dOperator = $request->get('d_operator');
        $dValue = $request->get('d_value');

        if(
            !(
                isset($dAttribute) &&
                isset($dOperator) &&
                isset($dValue)
            ) &&
            !(
                !isset($dAttribute) &&
                !isset($dOperator) &&
                !isset($dValue)
            )
        ) {
            return response()->json([
                'error' => 'Please provide either all dependency fields or none'
            ], 400);
        }

        $dependsOn = [
            $dAttribute => [
                'operator' => $dOperator,
                'value' => $dValue,
                'dependant' => $aid
            ]
        ];

        $contextAttribute->depends_on = json_encode($dependsOn);
        $contextAttribute->save();
        return response()->json(null, 204);
    }

    // DELETE

    public function deleteContextType($id) {
        ContextType::find($id)->delete();
        return response()->json(null, 204);
    }

    public function deleteAttribute($id) {
        Attribute::find($id)->delete();
        return response()->json(null, 204);
    }

    public function removeAttributeFromContextType($ctid, $aid) {
        $ca = ContextAttribute::where([
            ['attribute_id', '=', $aid],
            ['context_type_id', '=', $ctid]
        ])->first();

        if($ca === null){
            return response()->json([
                'error' => 'No ContextAttribute found'
            ], 400);
        }

        $pos = $ca->position;
        $ca->delete();

        $successors = ContextAttribute::where([
                ['position', '>', $pos],
                ['context_type_id', '=', $ctid]
            ])->get();
        foreach($successors as $s) {
            $s->position--;
            $s->save();
        }
        return response()->json(null, 204);
    }
}
