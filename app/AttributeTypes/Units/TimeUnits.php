<?php

namespace App\AttributeTypes\Units;

class TimeUnits extends UnitSystem {

    public function __construct() {
        parent::__construct('time', new BaseUnit('second', 's'));
        $this->add(new Unit('minute', 'm', function($value) { return $value * 60; }));
        $this->add(new Unit('hour', 'h', function($value) { return $value * 3600; }));
        $this->add(new Unit('day', 'd', function($value) { return $value * 86400; }));
        $this->add(new Unit('year', 'a', function($value) { return $value * 31536000; }));
    }
}