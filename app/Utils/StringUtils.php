<?php

namespace App\Utils;

class StringUtils {
    
    static function guard($data) : string {
        if(!is_string($data)) {
            throw new \Exception("Given data is not a string");
        }
        
        return trim($data);
    }
}