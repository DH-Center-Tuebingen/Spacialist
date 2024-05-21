<?php

namespace App\AttributeTypes\Units\Implementations;

use App\AttributeTypes\Units\Call;
use App\AttributeTypes\Units\Unit\BaseUnit;
use App\AttributeTypes\Units\SiPrefix\Si;
use App\AttributeTypes\Units\SiPrefix\SiPrefix;
use App\AttributeTypes\Units\Unit\SiUnit;
use App\AttributeTypes\Units\Unit\Unit;
use App\AttributeTypes\Units\UnitSystem;

use const App\AttributeTypes\Units\Constants\Imperial\INCH;
use const App\AttributeTypes\Units\Constants\Imperial\FOOT;
use const App\AttributeTypes\Units\Constants\Imperial\YARD;
use const App\AttributeTypes\Units\Constants\Imperial\ACRE;
use const App\AttributeTypes\Units\Constants\Imperial\MILE;


class AreaUnits extends UnitSystem {
    const SQUARE = 'square';
    const METRE = 'metre';

    private function makeSiLabel(SiPrefix $prefix = null): string {
        return self::SQUARE . "_{$prefix->getLabel()}" . self::METRE;
    }

    private function makeLabel(string $unit): string {
        return self::SQUARE . "_{$unit}";
    }

    public function __construct() {
        $squareMetre = new BaseUnit(join("_", [self::SQUARE, self::METRE]), 'm²');
        parent::__construct('area', $squareMetre);

        $this->addSiUnits();
        $this->addImperialUnits();
    }

    private function addSiUnits() {
        $this->addMultiple([
            new Unit($this->makeSiLabel(Si::$KILO), 'km²', Call::si(Si::$KILO, 2)),
            new Unit($this->makeSiLabel(Si::$CENTI), 'cm²', Call::si(Si::$CENTI, 2)),
        ]);
    }

    private function addImperialUnits() {
        $this->addMultiple([
            new Unit($this->makeLabel('inch'), 'in²', Call::multiply(INCH, 2)),
            new Unit($this->makeLabel('feet'), 'ft²', Call::multiply(FOOT, 2)),
            new Unit($this->makeLabel('yard'), 'yd²', Call::multiply(YARD, 2)),
            new Unit('acre', 'ac', Call::multiply(ACRE)),
            new Unit($this->makeLabel('mile'), 'mi²', Call::multiply(MILE, 2)),
        ]);
    }
}
