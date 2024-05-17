<?php

namespace App\AttributeTypes\Units\Implementations;

use App\AttributeTypes\Units\Unit\BaseUnit;
use App\AttributeTypes\Units\Unit\Unit;
use App\AttributeTypes\Units\UnitSystem;

class TemperatureUnits extends UnitSystem{
    
        public function __construct() {
            parent::__construct('temperature', new BaseUnit('kelvin', 'K'));
            
            $this->addMultiple([
                new Unit('celsius', '°C', function($value) { return $value + 273.15; }),
                new Unit('fahrenheit', '°F', function($value) { return ($value + 459.67) * 5/9; }),
                new Unit('réaumur', '°Ré', function($value) { return $value * 1.25 + 273.15; }),
            ]);
        }
}