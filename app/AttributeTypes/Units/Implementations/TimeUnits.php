<?php

namespace App\AttributeTypes\Units\Implementations;

use App\AttributeTypes\Units\Call;
use App\AttributeTypes\Units\Unit\BaseUnit;
use App\AttributeTypes\Units\Unit\Unit;;
use App\AttributeTypes\Units\UnitSystem;

class TimeUnits extends UnitSystem {

    public function __construct() {
        parent::__construct('time', new BaseUnit('second', 's'));
        $this->addMultiple([
            new Unit('minute', 'min', Call::multiply(60)),
            new Unit('hour', 'h', Call::multiply(3600)),
            new Unit('day', 'd', Call::multiply(86400)),
            new Unit('year', 'a', Call::multiply(31536000)),
        ]);
    }
}