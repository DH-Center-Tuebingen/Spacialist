<?php

namespace App\AttributeTypes;

class SerialAttribute extends AttributeBase
{
    protected static string $type = "serial";
    protected static bool $inTable = false;
    protected static ?string $field = "str_val";

    public static function fromImport(string $data) : mixed {
        return null;
    }
    public static function unserialize(mixed $data) : mixed {
        return false;
    }

    public static function serialize(mixed $data) : mixed {
        return $data;
    }
}