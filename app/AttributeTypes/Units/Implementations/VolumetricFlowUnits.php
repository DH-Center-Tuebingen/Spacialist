<?php

namespace App\AttributeTypes\Units\Implementations;

use App\AttributeTypes\Units\Constants\Imperial;
use App\AttributeTypes\Units\Constants\Si;
use App\AttributeTypes\Units\Unit;
use App\AttributeTypes\Units\UnitSystem;
use App\AttributeTypes\Units\UnitType;

class VolumetricFlowUnits extends UnitSystem {
    public function __construct() {
        parent::__construct('volumetric_flow', [
            Unit::createBase('cubic_metre_per_second', 'm³/s'),
            Unit::createUnit('litre_per_second'      , 'l/s', 10 ** (Si::DECI * 3)),
        ]);

    }

}
