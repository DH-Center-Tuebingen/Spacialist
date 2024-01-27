<?php

namespace App\AttributeTypes;

class BooleanAttribute extends AttributeBase
{
    protected static string $type = "boolean";
    protected static bool $inTable = true;
    protected static ?string $field = 'int_val';

    public static function fromImport(string $data) : mixed {
        return 
                $data == 1 || $data == '1' ||
                strtolower($data) == 'true' || strtolower($data) == 't' ||
                intval($data) > 0;
    }

    public static function unserialize(mixed $data) : mixed {
        return $data;
    }

    public static function serialize(mixed $data) : mixed {
        return $data;
    }
}
