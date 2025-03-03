<?php

namespace App\AttributeTypes;

use App\Exceptions\InvalidDataException;
use App\DataTypes\TimePeriod;
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
            throw InvalidDataException::requiredFormat('START;END', $data);
        }

        $start = intval(trim($parts[0]));
        $end = intval(trim($parts[1]));

        if(!NumberUtils::is_integer_string($parts[0]) || !NumberUtils::is_integer_string($parts[1])) {
            throw InvalidDataException::requireTypes("integer", $parts);
        }

        $start = intval($parts[0]);
        $end = intval($parts[1]);
        if($end < $start) {
            throw InvalidDataException::requireBefore($start, $end);
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
