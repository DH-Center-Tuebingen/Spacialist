<?php

namespace App\AttributeTypes\Units\Constants;

class Temperature {
    public static function CELSIUS_2_KELVIN() {
        return function($value) {
            return $value + 273.15;
        };
    }
    public static function FAHRENHEIT_2_KELVIN() {
        return function($value) {
            return ($value + 459.67) * 5/9;
        };
    }
    public static function REAUMUR_2_KELVIN() {
        return function($value) {
            return $value * 1.25 + 273.15;
        };
    }
}