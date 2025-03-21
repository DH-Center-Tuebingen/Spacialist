<?php

namespace App\AttributeTypes\Units\Implementations;

use App\AttributeTypes\Units\Constants\Imperial;
use App\AttributeTypes\Units\Constants\Si;
use App\AttributeTypes\Units\Unit;
use App\AttributeTypes\Units\UnitSystem;
use App\AttributeTypes\Units\UnitType;

class MassUnits extends UnitSystem
{

    public function __construct()
    {
        parent::__construct('mass', [
            Unit::createUnit('milligram', 'mg', Si::MILLI, 1, UnitType::SI),
            Unit::createBase( 'gram'     , 'g'),
            Unit::createUnit('kilogram' , 'kg', Si::KILO, 1, UnitType::SI),
            Unit::createUnit('ton'      , 't', Si::MEGA, 1, UnitType::SI),
        ]);

        $this->addImperialUnits();
    }

    private function addImperialUnits() {
        $this->addMultiple([
            Unit::createUnit('ounce', 'oz', Imperial::OUNCE_2_G),
            Unit::createUnit('pound', 'lb', Imperial::POUND_2_G),
        ]);
    }


} 