<?php

namespace App\AttributeTypes;

use App\Exceptions\InvalidDataException;
use Exception;

class IntegerAttribute extends AttributeBase
{
    protected static string $type = "integer";
    protected static bool $inTable = true;
    protected static ?string $field = 'int_val';

    private static function is_integer($data) {
        return is_int($data) || ctype_digit($data);
    }
    
    public static function fromImport(int|float|bool|string $data) : mixed {        
        if(!self::is_integer($data)) {
            throw new InvalidDataException("Given data is not an integer");
        }
        return intval($data);
    }

    public static function unserialize(mixed $data) : mixed {
        return $data;
    }

    public static function serialize(mixed $data) : mixed {
        info("Serializing integer");
        if(!self::is_integer($data)) {
            throw new Exception("Given data is not an integer");
        }
        
        return $data;
    }
}
