<?php

namespace App\AttributeTypes;

abstract class AttributeBase
{
    private static function getTypesFromFolder() : array {
        $fullnames = array_map(function($file) {
            return __NAMESPACE__ . '\\' . substr($file, 0, -4);
        }, scandir(base_path("app".DIRECTORY_SEPARATOR."AttributeTypes")));

        return array_values(
            array_filter($fullnames, function($path){
                return $path != __CLASS__ && class_exists($path);
            })
        );
    }

    public static function serialized() : array {
        return [
            'datatype' => static::$type,
            'in_table' => static::$inTable,
        ];
    }

    public static function getTypes(bool $inTable = false) {
        $types = self::getTypesFromFolder();

        $list = array_map(function($class) {
            return call_user_func("$class::serialized");
        }, $types);

        return $list;
    }

    public static function getMatchingClass(string $datatype) : mixed {
        $types = self::getTypesFromFolder();

        foreach($types as $class) {
            if($datatype == call_user_func("$class::getType")) {
                return new $class();
            }
        }

        return false;
    }

    public static function getType() : string {
        return static::$type;
    }

    public static function getInTable() : bool {
        return static::$inTable;
    }

    public static function getField() : string {
        return static::$field;
    }

    public static function getHasSelection() : string {
        return isset(static::$hasSelection) && static::$hasSelection;
    }

    public abstract function unserialize(string $data) : mixed;
    public abstract function serialize(mixed $data) : mixed;
}
