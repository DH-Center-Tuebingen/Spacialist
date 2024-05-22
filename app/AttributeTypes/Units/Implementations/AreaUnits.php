<?php

namespace App\AttributeTypes\Units\Implementations;

use App\AttributeTypes\Units\Constants\Imperial;
use App\AttributeTypes\Units\Constants\Si;
use App\AttributeTypes\Units\Unit;
use App\AttributeTypes\Units\UnitSystem;
use App\AttributeTypes\Units\UnitType;

class AreaUnits extends UnitSystem {
    private const /*int*/ DIM = 2;

    public function __construct() {
        parent::__construct('area', [
            Unit::createUnit('square_centimetre', 'cm²', Si::CENTI, self::DIM, UnitType::SI),
            Unit::createBase( 'square_metre'     , 'm²'),
            Unit::createUnit('square_kilometre' , 'km²', Si::KILO, self::DIM, UnitType::SI),
        ]);
        
        $this->addMultiple([
            Unit::createUnit('square_inch'      , 'in²', Imperial::INCH_2_M , self::DIM),
            Unit::createUnit('square_feet'      , 'ft²', Imperial::FOOT_2_M, self::DIM),
            Unit::createUnit('square_yard'      , 'yd²', Imperial::YARD_2_M, self::DIM),
            Unit::createUnit('acre'             , 'ac' , Imperial::ACRE_2_M2),
            Unit::createUnit('square_mile'      , 'mi²', Imperial::MILE_2_M, self::DIM),
        ]);
    }
}
