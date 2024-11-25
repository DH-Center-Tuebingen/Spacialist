<?php

namespace App\AttributeTypes;

use App\Exceptions\InvalidDataException;
use App\Geodata;
use Clickbar\Magellan\IO\Generator\WKT\WKTGenerator;
use App\Utils\StringUtils;
use Exception;

class GeographyAttribute extends AttributeBase
{
    protected static string $type = "geography";
    protected static bool $inTable = true;
    protected static ?string $field = 'geography_val';

    public static function parseImport(int|float|bool|string $data) : mixed {
        $data = StringUtils::useGuard(InvalidDataException::class)($data);
        $geodata = null;
        try {
            $geodata = Geodata::parseWkt($data);
        } catch(Exception $e) {
            throw InvalidDataException::invalidGeoData($data);
        }

        return $geodata;
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
