<?php

namespace App\AttributeTypes\Units\Implementations;

use App\AttributeTypes\Units\Call;
use App\AttributeTypes\Units\Unit\BaseUnit;
use App\AttributeTypes\Units\SiPrefix\Si;
use App\AttributeTypes\Units\Unit\SiUnit;
use App\AttributeTypes\Units\Unit\Unit;
use App\AttributeTypes\Units\UnitSystem;

use const App\AttributeTypes\Units\Constants\Imperial\INCH;
use const App\AttributeTypes\Units\Constants\Imperial\FOOT;
use const App\AttributeTypes\Units\Constants\Imperial\YARD;
use const App\AttributeTypes\Units\Constants\Imperial\ACRE;
use const App\AttributeTypes\Units\Constants\Imperial\MILE;

const SQUARE = 'square_';
const SQUARE_METRE =  SQUARE . 'metre';

class AreaUnits extends UnitSystem {

    public function __construct() {
        $squareMetre = new BaseUnit(SQUARE_METRE, 'm²');
        parent::__construct('area', $squareMetre);

        $this->addSiUnits($squareMetre);
        $this->addImperialUnits();
    }

    private function addSiUnits(BaseUnit $squareMetre) {
        $this->addMultiple([
            new SiUnit(Si::$KILO, $squareMetre),
            new SiUnit(Si::$CENTI, $squareMetre),
        ]);
    }

    private function addImperialUnits() {
        $this->addMultiple([
            new Unit(SQUARE . 'inch', 'in²', Call::multiply(INCH , 2)),
            new Unit(SQUARE . 'feet', 'ft²', Call::multiply(FOOT, 2)),
            new Unit(SQUARE . 'yard', 'yd²', Call::multiply(YARD, 2)),
            new Unit('acre', 'ac', Call::multiply(ACRE)),
            new Unit(SQUARE . 'mile', 'mi²', Call::multiply(MILE, 2)),
        ]);
    }
}
