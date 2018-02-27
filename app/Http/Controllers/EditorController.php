<?php

namespace App\Http\Controllers;

// use App\AvailableLayer;
use App\Attribute;
use App\AttributeValue;
use App\Context;
use App\ContextType;
use App\ThConcept;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EditorController extends Controller {
    // GET

    public function getContextTypeOccurrenceCount($id) {
        $cnt = Context::where('context_type_id', $id)->count();
        return response()->json($cnt);
    }

    public function getAttributeOccurrenceCount($id) {
        $cnt = AttributeValue::where('attribute_id', $id)->count();
        return response()->json($cnt);
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
            ]
        ]);
    }

    // POST

    public function addContextType(Request $request) {
        $this->validate($request, [
            'concept_url' => 'required|url|exists:th_concept',
            // 'geomtype' => 'required|geom_type'
        ]);

        $curl = $request->get('concept_url');
        // $geomtype = $request->get('geomtype');
        $cType = new ContextType();
        $cType->thesaurus_url = $curl;
        $cType->type = 0;
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

        return response()->json($cType);
    }

    public function addAttribute(Request $request) {
        $this->validate($request, [
            'label_id' => 'required|integer|exists:th_concept,id',
            'datatype' => 'required|string',
            'parent_id' => 'nullable|integer|exists:th_concept,id',
            'columns' => 'nullable|json'
        ]);

        $lid = $request->get('label_id');
        $datatype = $request->get('datatype');
        $curl = ThConcept::find($lid)->concept_url;
        $attr = new Attribute();
        $attr->thesaurus_url = $curl;
        $attr->datatype = $datatype;
        if($request->has('parent_id')) {
            $pid = $request->get('parent_id');
            $purl = ThConcept::find($pid)->concept_url;
            $attr->thesaurus_root_url = $purl;
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
                if(isset($col->parent_id)) {
                    $pid = $col->parent_id;
                    $purl = ThConcept::find($pid)->concept_url;
                    $childAttr->thesaurus_root_url = $purl;
                }
                $childAttr->parent_id = $attr->id;
                $childAttr->save();
            }
        }

        return response()->json($attr);
    }

    // DELETE

    public function deleteContextType($id) {
        ContextType::find($id)->delete();
    }

    public function deleteAttribute($id) {
        Attribute::find($id)->delete();
    }
}
