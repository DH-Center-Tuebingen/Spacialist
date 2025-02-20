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
        $geodata = null;
        try {
            $geodata = Geodata::fromWKT($data);
        } catch(Exception $e) {
            throw InvalidDataException::invalidGeoData($data);
        }

        return $geodata;
    }

    public static function unserialize(mixed $data): mixed {
        return Geodata::fromWKT($data);
    }

    public static function serialize(mixed $data): mixed {
        return Geodata::toWKT($data);
    }
}
