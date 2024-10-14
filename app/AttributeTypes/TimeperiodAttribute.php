<?php

namespace App\AttributeTypes;

use App\Exceptions\InvalidDataException;
use App\Models\TimePeriod;
use App\Utils\NumberUtils;
use App\Utils\StringUtils;

class TimeperiodAttribute extends AttributeBase
{
    protected static string $type = "timeperiod";
    protected static bool $inTable = true;
    protected static ?string $field = 'json_val';

    public static function parseImport(int|float|bool|string $data) : mixed {
        $data = StringUtils::useGuard(InvalidDataException::class)($data);
        $parts = array_map(fn($str) => trim($str), explode(';', $data));

        if(count($parts) != 2) {
            throw new InvalidDataException("Given data does not match this datatype's format: 'START;END'");
        }

        if(!NumberUtils::is_integer_string($parts[0]) || !NumberUtils::is_integer_string($parts[1])) {
            throw new InvalidDataException("Start and end date must be integer values:" . $parts[0] . " " . $parts[1]);
        }

        $start = intval($parts[0]);
        $end = intval($parts[1]);


        if($end < $start) {
            throw new InvalidDataException("Start date must not be after end data ($start, $end)");
        }
        
        return json_encode(new TimePeriod($start, $end));
    }

    public static function unserialize(mixed $data) : mixed {
        return EpochAttribute::unserialize($data);
    }

    public static function serialize(mixed $data) : mixed {
        return json_decode($data);
    }
}
