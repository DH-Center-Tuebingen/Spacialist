<?php

namespace App\AttributeTypes;

class RichtextAttribute extends AttributeBase
{
    protected static string $type = "richtext";
    protected static bool $inTable = false;
    protected static ?string $field = 'str_val';

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
