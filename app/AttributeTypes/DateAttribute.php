<?php

namespace App\AttributeTypes;

class DateAttribute extends AttributeBase
{
    protected static string $type = "date";
    protected static bool $inTable = true;
    protected static ?string $field = 'dt_val';

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
