<?php

namespace App\AttributeTypes;

class PercentageAttribute extends AttributeBase
{
    protected static string $type = "percentage";
    protected static bool $inTable = false;
    protected static ?string $field = 'int_val';

    public static function fromImport(string $data) : mixed {
        return IntegerAttribute::fromImport($data);
    }

    public static function unserialize(mixed $data) : mixed {
        return $data;
    }

    public static function serialize(mixed $data) : mixed {
        return $data;
    }
}
