<?php

namespace App\AttributeTypes\Units\Implementations;

use App\AttributeTypes\Units\Unit\BaseUnit;
use App\AttributeTypes\Units\Call;
use App\AttributeTypes\Units\Unit\Unit;
use App\AttributeTypes\Units\SiPrefix\Si;
use App\AttributeTypes\Units\Unit\SiUnit;
use App\AttributeTypes\Units\UnitSystem;

use const App\AttributeTypes\Units\Constants\Imperial\FLUID_OUNCE_US;
use const App\AttributeTypes\Units\Constants\Imperial\GALLON_US;
use const App\AttributeTypes\Units\Constants\Imperial\MILE;
use const App\AttributeTypes\Units\Constants\Imperial\PINT_US;

const CUBIC = 'qubic_';
const CUBIC_METRE =  CUBIC . 'metre';

class VolumeUnits extends UnitSystem {

    public function __construct() {
        $cubicMetre = new BaseUnit(CUBIC_METRE, 'm³');
        parent::__construct('volume', $cubicMetre);

        $this->addSiUnits($cubicMetre);
        $this->addImperialUnits();
    }

    private function addSiUnits(BaseUnit $cubicMetre) {
        $this->addMultiple([
            new SiUnit(Si::$KILO, $cubicMetre),
            new SiUnit(Si::$CENTI, $cubicMetre),
        ]);
    }

    private function addImperialUnits() {
        $this->addMultiple([
            new Unit('fluid_ounce_us', 'fl oz', Call::multiply(FLUID_OUNCE_US)),
            new Unit('pint_us', 'pt', Call::multiply(PINT_US)),
            new Unit('gallon_us', 'gal', Call::multiply(GALLON_US)),
            new Unit(CUBIC . 'mile', 'mi³', Call::multiply(MILE, 3)),
        ]);
    }
}
