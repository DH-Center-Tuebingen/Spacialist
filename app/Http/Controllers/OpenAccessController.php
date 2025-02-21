<?php

namespace App\Http\Controllers;

use App\Attribute;
use App\AttributeValue;
use App\Entity;
use App\EntityAttribute;
use App\EntityType;
use App\Preference;
use App\ThConcept;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class OpenAccessController extends Controller
{

    private function getImplementedAttributesList(){
        return [
            'string',
            'stringf',
            'richtext',
            'iconclass',
            'rism',
            'double',
            'integer',
            'boolean',
            'percentage',
            'string-sc',
            'geography',
            'entity'
        ];
    }
    
    private function getValueKey(string $datatype, mixed $attributeValue) : string {
        switch($datatype) {
            case 'boolean':
            case 'double':
            case 'integer':
            case 'percentage':
                return (string) $attributeValue;
            case 'entity':
                return $attributeValue['id'];
            default:
                return $attributeValue;
        }
    }

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
            $datatype = $attr->attribute->datatype;
            if(!in_array($datatype, $this->getImplementedAttributesList())) {
                continue;
            }
            $currData = [];
            $attributeValues = AttributeValue::where('attribute_id', $attr->attribute_id)->whereIn('entity_id', $entityIds)->get();

            foreach($attributeValues as $attrVal) {
                $value = $this->getValueKey($datatype, $attrVal->getValue());
                if(!array_key_exists($value, $currData)) {
                    $currData[$value] = 0;
                }
                $currData[$value]++;
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
        $users = User::withoutTrashed()->orderBy('id')->get();
        $delUsers = User::onlyTrashed()->orderBy('id')->get();

        return response()->json([
            'concepts' => $concepts,
            'preferences' => $preferences,
            'users' => $users,
            'deleted_users' => $delUsers,
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

    public function getEntity(Request $request, $id) {
        if(!Preference::hasPublicAccess()) {
            return response()->json();
        }

        try {
            $entity = Entity::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist')
            ], 400);
        }

        $metadata = $entity->getAllMetadata();

        return response()->json([
            'entity' => $entity,
            'metadata'=> $metadata,
        ]);
    }

    public function getEntityData(Request $request, $id) {
        if(!Preference::hasPublicAccess()) {
            return response()->json();
        }

        try {
            $entity = Entity::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist')
            ], 400);
        }

        $attributes = AttributeValue::whereHas('attribute', function (Builder $q) {
            $q->where('datatype', '!=', 'sql');
        })
            ->where('entity_id', $id)
            ->withoutModerated()
            ->get();

        // TODO simplify as soon as 0.10-fix-performance-table is merged!
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
            $a->value = $a->getValue();
            $data[$a->attribute_id] = $a;
        }

        return response()->json($data);
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
