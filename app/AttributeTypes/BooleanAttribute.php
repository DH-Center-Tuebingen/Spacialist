<?php

namespace App\AttributeTypes;

use App\Exceptions\InvalidDataException;

class BooleanAttribute extends AttributeBase {
    protected static string $type = "boolean";
    protected static bool $inTable = true;
    protected static ?string $field = 'int_val';

    public static function parseImport(int|float|bool|string $data): mixed {        
        
        $boolean = false;
        
        if(is_bool($data)) {
            $boolean = $data;
        } else if(is_numeric($data)) {
            floatval($data) > 0 ? $boolean = true : $boolean = false;
        } else if(is_string($data)) {
            $truthy = ['true', 't', 'x', 'wahr', 'w'];
            $string_val = strtolower(trim($data));
            
            for($i = 0; $i < count($truthy); $i++) {
                if($string_val === $truthy[$i]) {
                    $boolean = true;
                    break;
                }
            }
        } else {
            throw new InvalidDataException("Invalid data type for boolean attribute");
        }

        return $boolean;
    }

    public static function unserialize(mixed $data): mixed {
        return $data;
    }

    public static function serialize(mixed $data): mixed {
        return $data;
    }
}
