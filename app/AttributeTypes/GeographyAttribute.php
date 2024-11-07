<?php

namespace App\AttributeTypes;

use App\Geodata;

class GeographyAttribute extends AttributeBase
{
    protected static string $type = "geography";
    protected static bool $inTable = true;
    protected static ?string $field = 'geography_val';

    public static function fromImport(int|float|bool|string $data) : mixed {
        return Geodata::fromWKT($data);
    }

    public static function unserialize(mixed $data) : mixed {
        return Geodata::fromWKT($data);
    }

    public static function serialize(mixed $data) : mixed {
        return Geodata::toWKT($data);
    }
}
