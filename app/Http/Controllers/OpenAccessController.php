<?php

namespace App\Http\Controllers;

use App\Attribute;
use App\AttributeValue;
use App\Entity;
use App\EntityAttribute;
use App\EntityType;
use App\Preference;
use App\ThConcept;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class OpenAccessController extends Controller
{
    private function getAttributeValueCount($entityTypeId, $filteredEntityIds = []) : array {
        $attributes = EntityAttribute::with(['attribute'])
            ->where('entity_type_id', $entityTypeId)
            ->get();
        if(empty($filteredEntityIds)) {
            $entityIds = Entity::where('entity_type_id', $entityTypeId)
                ->get()
                ->pluck('id');
        } else {
            $entityIds = $filteredEntityIds;
        }

        $attrData = [];
        foreach($attributes as $attr) {
            if(!in_array($attr->attribute->datatype, ['string', 'stringf', 'richtext', 'iconclass', 'rism', 'double', 'integer', 'boolean', 'percentage', 'string-sc', 'geography', 'entity'])) {
                continue;
            }
            $currData = [];
            $attributeValues = AttributeValue::where('attribute_id', $attr->attribute_id)->whereIn('entity_id', $entityIds)->get();
            foreach($attributeValues as $attrVal) {
                $val = $attrVal->getValue();
                if(!array_key_exists($val, $currData)) {
                    $currData[$val] = 0;
                }
                $currData[$val]++;
                
            }
            $attrData[$attr->attribute_id] = $currData;
        }
        return $attrData;
    }

    // GET
    public function getGlobals(Request $request) {
        if(!Preference::hasPublicAccess()) {
         return response()->json();
        }
        $locale = App::getLocale();
        $concepts = ThConcept::getMap($locale);
        $preferences = Preference::getPreferences(true);

        return response()->json([
            'concepts' => $concepts,
            'preferences' => $preferences,
        ]);
    }

    public function getEntityTypes(Request $request) {
        if(!Preference::hasPublicAccess()) {
         return response()->json();
        }
        return response()->json(EntityType::all());
    }

    public function getAttributes(Request $request) {
        if(!Preference::hasPublicAccess()) {
         return response()->json();
        }

        $forEntityType = $request->query('entity_type', null);
        $withData = $request->query('with_data', false);
        if(isset($forEntityType)) {
            $attributes = EntityAttribute::with(['attribute', 'entity_type'])->where('entity_type_id', $forEntityType)->get();
            if($withData) {
                $attrData = $this->getAttributeValueCount($forEntityType);
                $attributes = [
                    'attributes' => $attributes,
                    'data' => $attrData,
                ];
            }
        } else {
            $attributes = EntityAttribute::with(['attribute', 'entity_type'])->get();
        }

        return response()->json($attributes);
    }

    // POST
    public function getFilterResults(Request $request, $page = 1) {
        if(!Preference::hasPublicAccess()) {
         return response()->json();
        }

        $types = $request->input('types', []);
        $attributes = $request->input('attributes', []);

        $results = [];

        info($types);
        $entityIds = empty($types) ? Entity::pluck('id') : Entity::whereIn('entity_type_id', $types)->pluck('id');
        info($entityIds);

        if(!empty($attributes)) {
            $attributeValues = AttributeValue::whereIn('entity_id', $entityIds)->whereIn('attribute_id', $attributes)->get();
            $entityIds = $attributeValues->pluck('entity_id');
        }
        $results = Entity::whereIn('id', $entityIds)->paginate(15);

        foreach($results as $entity) {
            foreach($entity->attributes as $attribute) {
                $attribute->value = $attribute->getAttributeValueFromEntityPivot();
                $name = $attribute->getEntityAttributeValueName();
                if(isset($name)) {
                    $attribute->name = $name;
                }
            }
        }

        return response()->json($results);
    }

    public function getFilterResultsForType(Request $request, $id, $page = 1) {
        if(!Preference::hasPublicAccess()) {
         return response()->json();
        }

        $filters = $request->input('filters', []);
        $isOr = $request->input('or', false);

        $query = Entity::where('entity_type_id', $id);

        foreach($filters as $key => $value) {
            $attribute = Attribute::find($key);
            $valueCol = AttributeValue::getValueColumn($attribute->datatype);
            if($isOr) {
                $query->orWhereHas('attributes', function($subq) use ($key, $value, $valueCol) {
                    $subq->where('id', $key)->whereIn($valueCol, $value);
                });
            } else {
                $query->whereHas('attributes', function($subq) use ($key, $value, $valueCol) {
                    $subq->where('attributes.id', $key)->whereIn($valueCol, $value);
                });
            }
        }
        
        $results = $query->with('attributes')->paginate();

        if($page == 1) {
            $attrData = $this->getAttributeValueCount($id, $query->get()->pluck('id'));
        } else {
            $attrData = [];
        }

        foreach($results as $entity) {
            foreach($entity->attributes as $attribute) {
                $attribute->value = $attribute->getAttributeValueFromEntityPivot();
                $name = $attribute->getEntityAttributeValueName();
                if(isset($name)) {
                    $attribute->name = $name;
                }
            }
        }

        return response()->json([
            'entities' => $results,
            'data' => $attrData,
        ]);
    }
}
