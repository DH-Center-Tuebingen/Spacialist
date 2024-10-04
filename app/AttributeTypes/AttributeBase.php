<?php

namespace App\AttributeTypes;

use App\Attribute;
use App\AttributeValue;
use App\Entity;
use App\EntityType;
use App\User;
use Illuminate\Support\Arr;

abstract class AttributeBase
{
    protected static string $type;
    protected static ?string $field;
    protected static bool $inTable;
    protected static bool $hasSelection;

    private static array $types = [
        "boolean" => BooleanAttribute::class,
        "date" => DateAttribute::class,
        "daterange" => DaterangeAttribute::class,
        "dimension" => DimensionAttribute::class,
        "double" => DoubleAttribute::class,
        "string-mc" => DropdownMultipleAttribute::class,
        "string-sc" => DropdownSingleAttribute::class,
        "entity" => EntityAttribute::class,
        "entity-mc" => EntityMultipleAttribute::class,
        "epoch" => EpochAttribute::class,
        "geography" => GeographyAttribute::class,
        "iconclass" => IconclassAttribute::class,
        "integer" => IntegerAttribute::class,
        "list" => ListAttribute::class,
        "percentage" => PercentageAttribute::class,
        "richtext" => RichtextAttribute::class,
        "rism" => RismAttribute::class,
        "serial" => SerialAttribute::class,
        "sql" => SqlAttribute::class,
        "string" => StringAttribute::class,
        "stringf" => StringfieldAttribute::class,
        "table" => TableAttribute::class,
        "timeperiod" => TimeperiodAttribute::class,
        "userlist" => UserlistAttribute::class,
        "url" => UrlAttribute::class,
        "si-unit" => SiUnitAttribute::class,
    ];

    public static function serialized() : array {
        return [
            'datatype' => static::$type,
            'in_table' => static::$inTable,
        ];
    }

    public static function getTypes(bool $serialized = false, array $filters = []) : array {
        if(count($filters) > 0) {
            $types = Arr::where(self::$types, function(string $attr, string $key) use($filters) {
                foreach($filters as $on => $value) {
                    if($on == "datatype") {
                        if($attr::getType() != $value) return false;
                    } else if($on == "in_table") {
                        if($attr::getInTable() != $value) return false;
                    } else if($on == "field") {
                        if($attr::getField() != $value) return false; 
                    } else if($on == "has_selection") {
                        if($attr::getHasSelection() != $value) return false;
                    }
                }
                return true;
            });
        } else {
            $types = self::$types;
        }

        if($serialized) {
            return array_values(array_map(function(string $class) {
                return $class::serialized();
            }, $types));
        } else {
            return $types;
        }
    }

    public static function getMatchingClass(string $datatype) : bool|AttributeBase {
        if(array_key_exists($datatype, self::$types)) {
            return new self::$types[$datatype]();
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

    public static function getInTable() : bool {
        return static::$inTable;
    }

    public static function getField() : ?string {
        return static::$field;
    }

    public static function getHasSelection() : string {
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
        if(method_exists($class, "handleOnAdd")) {
            $class::handleOnAdd($attr, $entityType, $user);
        }
    }

    protected static function importDataIsMissing($data):bool{
        if(!is_string($data)) return false;
        return trim($data) === "";
    }

    public abstract static function fromImport(int|float|bool|string $data) : mixed;
    public abstract static function unserialize(mixed $data) : mixed;
    public abstract static function serialize(mixed $data) : mixed;
}
