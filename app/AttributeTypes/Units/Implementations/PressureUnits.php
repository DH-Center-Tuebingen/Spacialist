<?php

namespace App\AttributeTypes\Units\Implementations;

use App\AttributeTypes\Units\Call;
use App\AttributeTypes\Units\Constants\Si;
use App\AttributeTypes\Units\Unit;
use App\AttributeTypes\Units\UnitSystem;
use App\AttributeTypes\Units\UnitType;

class PressureUnits extends UnitSystem {
    public function __construct() {
        // Pascal
        parent::__construct('pressure', [
            Unit::createBase('pascal'     , 'Pa'                              ),
            Unit::createUnit('kilopascal' , 'kPa',  Si::KILO,  1, UnitType::SI),
            Unit::createUnit('hectopascal', 'hPa',  Si::HECTO, 1, UnitType::SI),
        ]);

        // Bar
        $this->addMultiple([
            Unit::createUnit('bar'     , 'bar' , 1e5),
            Unit::createUnit('decibar' , 'dbar', 1e4), // Used in oceanography
            Unit::createUnit('millibar', 'mbar', 1e2),
        ]);

        // Various
        $this->addMultiple([
            Unit::createUnit('pound_per_square_inch', 'psi' , 6894.757),
            Unit::createUnit('torr'                 , 'Torr', 133.3224),
            Unit::createUnit('technical_atmosphere' , 'at'  , 98066.5 ),
            Unit::createUnit('standard_atmosphere'  , 'atm' , 101325  ),
        ]);
    }
}
