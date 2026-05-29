<?php

namespace App\AttributeTypes;

use App\Attribute;
use App\AttributeValue;
use App\Entity;
use App\EntityType;
use App\Exceptions\InvalidDataException;
use App\Registries\AttributeRegistry;
use App\User;
use App\Utils\StringUtils;

abstract class AttributeBase
{
    protected static string $type;
    protected static ?string $field;
    protected static ?string $label;
    protected static bool $inTable;
    protected static bool $hasSelection;

    public static function serialized() : array {
        return [
            'datatype' => static::$type,
            'in_table' => static::$inTable,
        ];
    }

    public static function getMatchingClass(string $datatype) : bool|AttributeBase {
        $types = AttributeRegistry::getTypes();
        if(array_key_exists($datatype, $types)) {
            return new $types[$datatype]['class']();
        }

        return false;
    }

    public static function getFieldFromType(string $datatype) : null|string {
        $class = self::getMatchingClass($datatype);
        return $class !== false ? $class::getField() : null;
    }

    public static function getType() : string {
        return static::$type;
    }

    public static function getLabel() : ?string {
        return isset(static::$label) ? static::$label : null;
    }

    public static function getInTable() : bool {
        return static::$inTable;
    }

    public static function getField() : ?string {
        return static::$field;
    }

    public static function getHasSelection() : bool {
        return isset(static::$hasSelection) && static::$hasSelection;
    }

    public static function serializeValue(AttributeValue $value) : mixed {
        $class = self::getMatchingClass($value->attribute->datatype);
        if($class !== false) {
            $field = $class::getField();
            return $class::serialize($value->{$field});
        } else {
            return null;
        }
    }

    public static function serializeExportData(AttributeValue $value) : mixed {
        $class = self::getMatchingClass($value->attribute->datatype);
        if($class !== false) {
            $field = $class::getField();
            return $class::parseExport($value->{$field});
        } else {
            return '';
        }
    }

    public static function onCreateHandler(Entity $entity, User $user) : void {
        $attributes = $entity->entity_type->attributes()->get();
        foreach($attributes as $attr) {
            $class = self::getMatchingClass($attr->datatype);
            if($class && method_exists($class, "handleOnCreate")) {
                $class::handleOnCreate($entity, $attr, $user);
            }
        }
    }

    public static function onAddHandler(Attribute $attr, EntityType $entityType, User $user) : void {
        $class = self::getMatchingClass($attr->datatype);
        if($class !== false && method_exists($class, "handleOnAdd")) {
            $class::handleOnAdd($attr, $entityType, $user);
        }
    }

    private static function importDataIsEmpty(int|float|bool|string $data) : bool {
        if(!is_string($data)) return false;
        return trim($data) === "";
    }

    public static function fromImport(int|float|bool|string $data) : mixed {
        if(self::importDataIsEmpty($data)) {
            return null;
        }
        return static::parseImport($data);
    }

    public static function parseImport(int|float|bool|string $data) : mixed {
        return StringUtils::useGuard(InvalidDataException::class)($data);
    }

    public static function parseExport(mixed $data) : string {
        return strval($data);
    }

    public abstract static function unserialize(mixed $data) : mixed;
    public abstract static function serialize(mixed $data) : mixed;
}
