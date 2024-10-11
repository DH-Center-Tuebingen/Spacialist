<?php

namespace App\AttributeTypes;

use App\Exceptions\InvalidDataException;
use App\Utils\NumberUtils;
use App\Utils\StringUtils;

class RismAttribute extends AttributeBase
{
    protected static string $type = "rism";
    protected static bool $inTable = true;
    protected static ?string $field = 'str_val';

    public static function parseImport(int|float|bool|string $data) : mixed {
        $number = NumberUtils::useStringIntegerGuard(InvalidDataException::class)($data);
        if($number < 0){
            throw new InvalidDataException("Given data is not a positive integer");
        }
        return $number; 
    }

    public static function unserialize(mixed $data) : mixed {
        return $data;
    }

    public static function serialize(mixed $data) : mixed {
        return $data;
    }
}
