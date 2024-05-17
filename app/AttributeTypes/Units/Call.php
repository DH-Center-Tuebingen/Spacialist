<?php

namespace App\AttributeTypes\Units;

class Call {

    public static function identity() {
        return function ($value) {
            return $value;
        };
    }

    public static function multiply($factor, $dimension = 1) {
        return function ($value) use ($factor, $dimension) {
            return $value * ($factor ** $dimension);
        };
    }

    public static function si($power, $dimension = 1) {
        return function ($value) use ($power, $dimension) {
            return $value * pow(10, $power * $dimension);
        };
    }
}
