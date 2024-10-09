<?php

namespace App\AttributeTypes;

use App\Exceptions\InvalidDataException;
use App\Geodata;
use App\Utils\StringUtils;
use Clickbar\Magellan\Exception\UnknownWKTTypeException;
use Exception;

class GeographyAttribute extends AttributeBase
{
    protected static string $type = "geography";
    protected static bool $inTable = true;
    protected static ?string $field = 'geography_val';

    public static function fromImport(int|float|bool|string $data) : mixed {
        $data = StringUtils::useGuard(InvalidDataException::class)($data);
        if(self::importDataIsEmpty($data)) return null;        
        
        $geodata = null;
        try{
            $geodata = Geodata::parseWkt($data);
        } catch(Exception $e) {
            throw new InvalidDataException("Invalid geography data: " . $data);
        }
        
        return $geodata;
    }

    public static function unserialize(mixed $data) : mixed {
        return Geodata::parseWkt($data);
    }

    public static function serialize(mixed $data) : mixed {
        return $data->toWKT();
    }
}
