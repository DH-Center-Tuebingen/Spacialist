<?php

namespace App\AttributeTypes\Units\Implementations;

use App\AttributeTypes\Units\Constants\Time;
use App\AttributeTypes\Units\Unit;
use App\AttributeTypes\Units\UnitSystem;

class TimeUnits extends UnitSystem {

    public function __construct() {
        parent::__construct('time', [
            Unit::createBase( 'second', 's'),
            Unit::createUnit('minute', 'min', Time::MINUTE_2_SECOND),
            Unit::createUnit('hour'  , 'h', Time::HOUR_2_SECOND),
            Unit::createUnit('day'   , 'd', Time::DAY_2_SECOND),
            Unit::createUnit('year'  , 'a', Time::YEAR_2_SECOND),
        ]);
    }
}