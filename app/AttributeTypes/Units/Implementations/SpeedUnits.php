<?php

namespace App\AttributeTypes\Units\Implementations;

use App\AttributeTypes\Units\Unit;
use App\AttributeTypes\Units\UnitSystem;

class SpeedUnits extends UnitSystem {

    public function __construct() {
        parent::__construct('speed', [
            Unit::createBase(   'm/s'     ,   'm/s'                               ),
            Unit::createUnit(   'km/h' ,   'km/h' , self::factorFromTimeAndLengthUnits('hour', 'kilometre') ),
            Unit::createUnit(   'mph' ,   'mph' , self::factorFromTimeAndLengthUnits('hour', 'mile') ), 
        ]);
    }
    
    public static function factorFromTimeAndLengthUnits($time, $length) {
        $lengthUnits = new LengthUnits();
        $timeUnits = new TimeUnits();
        
        return $lengthUnits->getByLabel($length)->is(1) / $timeUnits->getByLabel($time)->is(1);
    }
}
