<?php

namespace App\AttributeTypes\Units\Implementations;

use App\AttributeTypes\Units\Unit\BaseUnit;
use App\AttributeTypes\Units\Call;
use App\AttributeTypes\Units\Unit\Unit;
use App\AttributeTypes\Units\SiPrefix\Si;
use App\AttributeTypes\Units\Unit\SiUnit;
use App\AttributeTypes\Units\UnitSystem;

use const App\AttributeTypes\Units\Constants\Imperial\FOOT;
use const App\AttributeTypes\Units\Constants\Imperial\INCH;
use const App\AttributeTypes\Units\Constants\Imperial\MILE;
use const App\AttributeTypes\Units\Constants\Imperial\YARD;

const METRE = 'metre';

class LengthUnits extends UnitSystem {

    public function __construct() {
        $metre = new BaseUnit(METRE, 'm');
        parent::__construct('length', new BaseUnit(METRE, 'm'));

        $this->addSiUnits($metre);
        $this->addImperialUnits();

        $this->add(new Unit('light_year', 'ly', Call::multiply(9460730472580800)));
    }

    private function addSiUnits(BaseUnit $metre) {
        $this->addMultiple([
            new SiUnit(Si::$KILO, $metre),
            new SiUnit(Si::$CENTI, $metre),
            new SiUnit(Si::$MILLI, $metre),
            new SiUnit(Si::$MICRO, $metre),
            new SiUnit(Si::$NANO, $metre),
        ]);
    }

    private function addImperialUnits(){
        $this->addMultiple([
            new Unit('inch', 'in', Call::multiply(INCH)),
            new Unit('feet', 'ft', Call::multiply(FOOT)),
            new Unit('yard', 'yd', Call::multiply(YARD)),
            new Unit('mile', 'mi', Call::multiply(MILE)),
        ]);
    }
}
