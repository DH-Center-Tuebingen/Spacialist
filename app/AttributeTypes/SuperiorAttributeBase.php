<?php

namespace App\AttributeTypes;

use App\Exceptions\InvalidDataException;
use App\Model\Attributes\AttributeValue\AttributeValue;
use Illuminate\Database\Eloquent\Model;

abstract class SuperiorAttributeBase extends AttributeBase
{
    protected static string $modelClass;

    /**
     * Get the model class for this attribute type.
     */
    public static function getModelClass(): string
    {
        return static::$modelClass;
    }

    /**
     * Get the model class for a given datatype.
     */
    public static function getModelClassForType(string $datatype): ?string
    {
        $class = self::getMatchingClass($datatype);
        if($class !== false && method_exists($class, 'getModelClass')) {
            return $class::getModelClass();
        }
        return null;
    }

    /**
     * Serialize a value from a typed AttributeValue model.
     * Overrides parent to use $value->value instead of $value->{$field}.
     */
    public static function serializeValue(\App\AttributeValue|Model $value): mixed
    {
        $class = self::getMatchingClass($value->attribute->datatype);
        if($class !== false) {
            return $class::serialize($value->value);
        }
        return null;
    }

    /**
     * Serialize export data from a typed AttributeValue model.
     * Overrides parent to use $value->value instead of $value->{$field}.
     */
    public static function serializeExportData(\App\AttributeValue|Model $value): mixed
    {
        $class = self::getMatchingClass($value->attribute->datatype);
        if($class !== false) {
            return $class::parseExport($value->value);
        }
        return '';
    }

    /**
     * Create an AttributeValue instance for this type.
     */
    public static function createValue(int $entityId, int $attributeId, mixed $value, int $userId, ?int $certainty = null): AttributeValue
    {
        $modelClass = static::getModelClass();
        
        return $modelClass::firstOrNew([
            'entity_id' => $entityId,
            'attribute_id' => $attributeId,
        ], [
            'value' => static::serialize($value),
            'certainty' => $certainty,
            'user_id' => $userId,
        ]);
    }
}
