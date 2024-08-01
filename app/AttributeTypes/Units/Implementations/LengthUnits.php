<?php

namespace App\AttributeTypes\Units\Implementations;

use App\AttributeTypes\Units\Constants\Imperial;
use App\AttributeTypes\Units\Constants\General;
use App\AttributeTypes\Units\Constants\Si;
use App\AttributeTypes\Units\Unit;
use App\AttributeTypes\Units\UnitSystem;
use App\AttributeTypes\Units\UnitType;

class LengthUnits extends UnitSystem {

    public function __construct() {
        parent::__construct('length', [
            Unit::createUnit('nanometre' , 'nm' , Si::NANO  , 1, UnitType::SI),
            Unit::createUnit('micrometre', 'µm' , Si::MICRO , 1, UnitType::SI),
            Unit::createUnit('millimetre', 'mm' , Si::MILLI , 1, UnitType::SI),
            Unit::createUnit('centimetre', 'cm' , Si::CENTI , 1, UnitType::SI),
            Unit::createBase('metre'     , 'm'                               ),
            Unit::createUnit('kilometre' , 'km' , Si::KILO  , 1, UnitType::SI),
        ]);

        $this->addMultiple([
            Unit::createUnit('inch'      , 'in', Imperial::INCH_2_M),
            Unit::createUnit('feet'      , 'ft', Imperial::FOOT_2_M),
            Unit::createUnit('yard'      , 'yd', Imperial::YARD_2_M),
            Unit::createUnit('mile'      , 'mi', Imperial::MILE_2_M),
        ]);
    }
}
