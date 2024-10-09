<?php

namespace App\AttributeTypes;

use App\Exceptions\InvalidDataException;
use Exception;

class IntegerAttribute extends AttributeBase
{
    protected static string $type = "integer";
    protected static bool $inTable = true;
    protected static ?string $field = 'int_val';

    
    private static function is_numeric_string($data) {
        return preg_match('/^-?\d+$/', $data);
    }
    
    private static function is_integer_too_big(string $data) {
        // Compare with PHP_INT_MAX and PHP_INT_MIN using bccomp for arbitrary precision
        if(bccomp($data, (string)PHP_INT_MAX) === 1 || bccomp($data, (string)PHP_INT_MIN) === -1) {
            return true; 
        }

        return false; 
    }
    
    public static function fromImport(int|float|bool|string $data) : mixed { 
        
        $not_an_integer_error = "Data is not an integer: " . $data;
        
        if(is_int($data)) {
            return $data;
        }else if(is_string($data)){
            $data = trim($data);
            if($data === "") {
                return null;
            }
            
            if(!self::is_numeric_string($data)) {
                throw new InvalidDataException($not_an_integer_error);
            }
            
            if(self::is_integer_too_big($data)) {
                throw new InvalidDataException("Integer is too big: " . $data);
            }
            
            return intval($data);
        }else{
            throw new InvalidDataException($not_an_integer_error);
        }
    }

    public static function unserialize(mixed $data) : mixed {
        return $data;
    }

    public static function serialize(mixed $data) : mixed {
        if(is_int($data)) {
            return $data;   
        }else if(is_string($data)) {
            $data = trim($data);
            if(!self::is_numeric_string($data)){
                throw new Exception("Given data is not an integer");
            }
            $data = intval($data);
        }else{
            throw new Exception("Given data is not an integer");
        }
        
        return $data;
    }
}
