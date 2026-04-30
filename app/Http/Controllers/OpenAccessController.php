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

class OpenAccessController extends Controller {
    private static $supported_attributes = [
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
        'entity',
    ];

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
        return response()->json(
            EntityType::withCount('entities')
            ->get()
        );
    }

    public function getAttributeValuesForEntityType(Request $request, EntityType $entityType) {
        if(!Preference::hasPublicAccess()) {
            return response()->json();
        }

        $attributes = EntityAttribute::with('attribute')->where('entity_type_id', $entityType->id)->get();
        $attributeValues = [];
        foreach($attributes as $attr) {
            if(in_array($attr->attribute->datatype, self::$supported_attributes)) {
                $valueColumn = AttributeValue::getValueColumn($attr->attribute->datatype);
                // [VR]: Adding count of attribute values makes no sense as long
                // as we do not calculate it based on selected filters
                // Thus I exclude it from this query, but it can easily be added again
                // 1. add ->selectRaw('COUNT(*) as count')
                // 2. replace ->pluck($valueColumn) with ->pluck('count', $valueColumn)
                $values = AttributeValue::select($valueColumn)
                    ->where('attribute_id', $attr->attribute_id)
                    ->whereHas('entity', function (Builder $q) use ($entityType) {
                        $q->where('entity_type_id', $entityType->id);
                    })
                    ->groupBy($valueColumn)
                    ->get()
                    ->pluck($valueColumn);
                $attributeValues[$attr->attribute_id] = $values;
            }
        }

        return response()->json($attributeValues);

    }

    public function getAttributes(Request $request) {
        if(!Preference::hasPublicAccess()) {
            return response()->json();
        }

        $forEntityType = $request->query('entity_type', null);
        $attributeQuery = EntityAttribute::with(['attribute', 'entity_type']);
        if(isset($forEntityType)) {
            $attributes = $attributeQuery->where('entity_type_id', $forEntityType);
        }
        $attributes = $attributeQuery->get();

        return response()->json($attributes);
    }

    public function getEntity(Request $request, int $id) {
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

        $data = [];
        foreach($attributes as $a) {
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
        $entityIds = empty($types) ? Entity::pluck('id') : Entity::whereIn('entity_type_id', $types)->pluck('id');

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

        foreach($results as $key => $entity) {
            $attributePivots = $entity->getData();
            foreach($entity->attributes as $attribute) {
                $data = array_find($attributePivots, function($pivot) use ($attribute) {
                    return $pivot->attribute_id == $attribute->id;
                });
                $attribute->value = isset($data) ? $data->value : null;
            }
        }

        return response()->json($results);
    }
}
