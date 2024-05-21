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

class VolumeUnits extends UnitSystem {

    const CUBIC = 'cubic_';
    const METRE = 'metre';
    const CUBIC_METRE =  self::CUBIC . self::METRE;

    public function __construct() {
        $cubicMetre = new BaseUnit(self::CUBIC_METRE, 'm³');
        parent::__construct('volume', $cubicMetre);

        $this->addSiUnits($cubicMetre);
        $this->addImperialUnits();
    }

    private function addSiUnits(BaseUnit $cubicMetre) {
        $this->addMultiple([
            new Unit('litre', 'l', Call::si(Si::$DECI, 3)),
            new Unit('millilitre', 'ml', Call::si(Si::$CENTI, 3)),
        ]);
    }

    private function addImperialUnits() {
        $this->addMultiple([
            new Unit('fluid_ounce_us', 'fl oz', Call::multiply(FLUID_OUNCE_US)),
            new Unit('pint_us', 'pt', Call::multiply(PINT_US)),
            new Unit('gallon_us', 'gal', Call::multiply(GALLON_US)),
            new Unit(self::CUBIC . 'mile', 'mi³', Call::multiply(MILE, 3)),
        ]);
    }
}
