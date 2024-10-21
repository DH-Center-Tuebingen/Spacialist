<?php

namespace App\AttributeTypes;

use App\Exceptions\InvalidDataException;
use App\Utils\NumberUtils;
use Exception;

class IntegerAttribute extends AttributeBase
{
    protected static string $type = "integer";
    protected static bool $inTable = true;
    protected static ?string $field = 'int_val';

    public static function parseImport(int|float|bool|string $data) : mixed {
        return NumberUtils::useStringIntegerGuard(InvalidDataException::class)($data);
    }

    public static function unserialize(mixed $data) : mixed {
        return $data;
    }

    public static function serialize(mixed $data) : mixed {
        if(is_int($data)) {
            return $data;   
        }else if(is_string($data)) {
            $data = trim($data);
            if(!NumberUtils::is_integer_string($data)){
                throw new Exception("Given data is not an integer");
            }
            $data = intval($data);
        }else{
            throw new Exception("Given data is not an integer");
        }

        return $data;
    }
}
