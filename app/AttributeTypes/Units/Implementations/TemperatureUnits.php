<?php

namespace App\AttributeTypes\Units\Implementations;

use App\AttributeTypes\Units\Constants\Temperature as Temp;
use App\AttributeTypes\Units\Unit;
use App\AttributeTypes\Units\UnitSystem;

class TemperatureUnits extends UnitSystem{
    
        public function __construct() {
            parent::__construct('temperature', [
                Unit::createBase('kelvin'    , 'K'),
                Unit::createWith('celsius'   , '°C', Temp::CELSIUS_2_KELVIN()),
                Unit::createWith('fahrenheit', '°F', Temp::FAHRENHEIT_2_KELVIN()),
                Unit::createWith('réaumur'   , '°Ré', Temp::REAUMUR_2_KELVIN()),
            ]);
        }
}