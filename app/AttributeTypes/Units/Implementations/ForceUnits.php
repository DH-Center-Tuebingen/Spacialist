<?php

namespace App\AttributeTypes\Units\Implementations;

use App\AttributeTypes\Units\Constants\Si;
use App\AttributeTypes\Units\Unit;
use App\AttributeTypes\Units\UnitSystem;
use App\AttributeTypes\Units\UnitType;

class ForceUnits extends UnitSystem {
    public function __construct() {
        parent::__construct('force', [
            Unit::createBase('newton'    , 'N'                            ),
            Unit::createUnit('kilonewton', 'kN', Si::KILO, 1, UnitType::SI),
        ]);
    }
}
