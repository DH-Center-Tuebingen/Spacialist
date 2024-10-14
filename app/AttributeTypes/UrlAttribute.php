<?php

namespace App\AttributeTypes;

class UrlAttribute extends AttributeBase
{
    protected static string $type = "url";
    protected static bool $inTable = true;
    protected static ?string $field = 'str_val';

    public static function unserialize(mixed $data) : mixed {
        return $data;
    }

    public static function serialize(mixed $data) : mixed {
        return $data;
    }
}
