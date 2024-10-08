<?php

namespace App\AttributeTypes;

class RismAttribute extends AttributeBase
{
    protected static string $type = "rism";
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
