<?php

namespace App\AttributeTypes\Units\Implementations;

use App\AttributeTypes\Units\Constants\Imperial;
use App\AttributeTypes\Units\Constants\Si;
use App\AttributeTypes\Units\Unit;
use App\AttributeTypes\Units\UnitSystem;
use App\AttributeTypes\Units\UnitType;

class VolumeUnits extends UnitSystem {
    private const DIM = 3;

    public function __construct() {
        parent::__construct('volume', [
            Unit::createBase( 'cubic_metre', 'm³'),
            Unit::createUnit('litre'      , 'l', Si::DECI, self::DIM, UnitType::SI),
            Unit::createUnit('millilitre' , 'ml', Si::CENTI, self::DIM, UnitType::SI),
        ]);

        $this->addImperialUnits();
    }

    private function addImperialUnits() {
        $this->addMultiple([
            Unit::createUnit('fluid_ounce_us', 'fl oz', Imperial::FLUID_OUNCE_US),
            Unit::createUnit('pint_us'       , 'pt', Imperial::PINT_US),
            Unit::createUnit('gallon_us'     , 'gal', Imperial::GALLON_US),
            Unit::createUnit('cubic_mile'    , 'mi³', Imperial::MILE_2_M, self::DIM),
        ]);
    }
}
