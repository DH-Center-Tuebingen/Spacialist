<?php

namespace App\AttributeTypes\Units\Implementations;

use App\AttributeTypes\Units\Unit\BaseUnit;
use App\AttributeTypes\Units\Call;
use App\AttributeTypes\Units\SiPrefix\Si;
use App\AttributeTypes\Units\Unit\SiUnit;
use App\AttributeTypes\Units\Unit\Unit;
use App\AttributeTypes\Units\UnitSystem;

class MassUnits extends UnitSystem
{

    public function __construct()
    {
        // For simplicity and concistency we use g instead of kg as the base unit
        $grams = new BaseUnit('grams', 'g');
        parent::__construct('mass', $grams);

        $this->addSiUnits($grams);
        $this->addImperialUnits();
    }

    private function addSiUnits(BaseUnit $grams)
    {
        $this->addMultiple([
            new Unit('ton', 't', Call::Si(Si::$MEGA->getPower())),
            new SiUnit(Si::$KILO, $grams),
            new SiUnit(Si::$MILLI, $grams),
        ]);
    }

    private function addImperialUnits(){
        $this->addMultiple([
            new Unit('ounce', 'oz', Call::multiply(28.349523125)),
            new Unit('pound', 'lb', Call::multiply(453.59237)),
        ]);
    }


}