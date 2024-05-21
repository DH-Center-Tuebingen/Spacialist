<?php

namespace App\AttributeTypes\Units;

use App\AttributeTypes\Units\SiPrefix\SiPrefix;

class Call {

    public static function identity(): callable {
        return function ($value) {
            return $value;
        };
    }

    public static function multiply(float $factor, float $dimension = 1): callable {
        return function ($value) use ($factor, $dimension) {
            return $value * ($factor ** $dimension);
        };
    }

    public static function si(SiPrefix $si, float $dimension = 1): callable {
        $power = $si->getPower();
        return function ($value) use ($power, $dimension) {
            return $value * 10 ** ($power * $dimension);
        };
    }
}
