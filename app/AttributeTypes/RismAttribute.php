<?php

namespace App\AttributeTypes;

use App\Exceptions\InvalidDataException;
use App\Utils\NumberUtils;

class RismAttribute extends AttributeBase
{
    protected static string $type = "rism";
    protected static bool $inTable = true;
    protected static ?string $field = 'str_val';

    public static function parseImport(int|float|bool|string $data) : mixed {
        $number = trim($data);
        if(is_bool($data) || !NumberUtils::is_unsigned_integer_string($number)) {
            throw new InvalidDataException("Given data is not only digits");
        }
        return (string)$number;
    }

    public static function unserialize(mixed $data) : mixed {
        return $data;
    }

    public static function serialize(mixed $data) : mixed {
        return $data;
    }
}
