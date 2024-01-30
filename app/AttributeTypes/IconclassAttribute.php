<?php

namespace App\AttributeTypes;

class IconclassAttribute extends AttributeBase
{
    protected static string $type = "iconclass";
    protected static bool $inTable = true;
    protected static ?string $field = 'int_val';

    public static function fromImport(string $data) : mixed {
        return $data;
    }

    public static function unserialize(mixed $data) : mixed {
        return $data;
    }

    public static function serialize(mixed $data) : mixed {
        return $data;
    }
}