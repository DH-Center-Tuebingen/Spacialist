<?php

namespace App\AttributeTypes;

use App\Exceptions\InvalidDataException;
use App\Geodata;
use App\Utils\StringUtils;
use Exception;

class GeographyAttribute extends AttributeBase
{
    protected static string $type = "geography";
    protected static bool $inTable = true;
    protected static ?string $field = 'geography_val';

    public static function parseImport(int|float|bool|string $data): mixed {
        $data = StringUtils::useGuard(InvalidDataException::class)($data);

        // Try to parse as WKT string
        $geodata = Geodata::fromWKT($data);
        if(isset($geodata)) {
            return $geodata;
        }

        // Try to parse as WKB string
        $geodata = Geodata::fromWKB($data);
        if(isset($geodata)) {
            return $geodata;
        }

        // Throw an exception if the data is neither WKT nor WKB
        throw InvalidDataException::invalidGeoData($data);
    }

    public static function unserialize(mixed $data): mixed {
        return Geodata::fromWKT($data);
    }

    public static function serialize(mixed $data): mixed {
        return Geodata::toWKT($data);
    }
}
