<?php

namespace App\AttributeTypes\Units\Implementations;

use App\AttributeTypes\Units\Unit;
use App\AttributeTypes\Units\UnitSystem;

class SpeedUnits extends UnitSystem {

    public function __construct() {
        parent::__construct('speed', [
            Unit::createBase('metre_per_second'  , 'm/s'                                                           ),
            Unit::createUnit('kilometre_per_hour', 'km/h' , self::factorFromTimeAndLengthUnits('hour', 'kilometre')),
            Unit::createUnit('mile_per_hour'     , 'mph'  , self::factorFromTimeAndLengthUnits('hour', 'mile')     ),
        ]);
    }

    public static function factorFromTimeAndLengthUnits(string $time, string $length) : float {
        $lengthUnits = new LengthUnits();
        $timeUnits = new TimeUnits();

        return $lengthUnits->getByLabel($length)->is(1) / $timeUnits->getByLabel($time)->is(1);
    }
}
