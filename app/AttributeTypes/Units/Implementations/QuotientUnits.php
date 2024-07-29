<?php

namespace App\AttributeTypes\Units\Implementations;

use App\AttributeTypes\Units\Unit;
use App\AttributeTypes\Units\UnitSystem;

class QuotientUnits extends UnitSystem
{

    public function __construct()
    {
        parent::__construct('quotient', [
            Unit::createUnit('percent', '%', 1e4),
            Unit::createBase('parts per million'     , 'ppm'),
        ]);
    }



} 