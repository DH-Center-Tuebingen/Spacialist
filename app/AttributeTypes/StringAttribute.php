<?php

namespace App\AttributeTypes;

class StringAttribute extends AttributeBase
{
    protected static string $type = "string";
    protected static bool $inTable = true;
    protected static ?string $field = 'str_val';

    public static function fromImport(int|float|bool|string $data) : mixed {
        return $data;
    }

    public static function unserialize(mixed $data) : mixed {
        return $data;
    }

    public static function serialize(mixed $data) : mixed {
        return $data;
    }
}
