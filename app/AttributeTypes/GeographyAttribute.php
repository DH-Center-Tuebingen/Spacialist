<?php

namespace App\AttributeTypes;

use App\Geodata;
use Clickbar\Magellan\IO\Generator\WKT\WKTGenerator;

class GeographyAttribute extends AttributeBase
{
    protected static string $type = "geography";
    protected static bool $inTable = true;
    protected static ?string $field = 'geography_val';

    public static function fromImport(int|float|bool|string $data) : mixed {
        return Geodata::parseWkt($data);
    }

    public static function unserialize(mixed $data) : mixed {
        return Geodata::parseWkt($data);
    }

    public static function serialize(mixed $data) : mixed {
        // TODO already fixed in 0.11-feat-showcase with the following code:
        // return Geodata::toWKT($data);
        return (new WKTGenerator())->generate($data);
    }
}
