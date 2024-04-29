<?php

namespace App\AttributeTypes\Units;

class UnitValue {

    private float $value;
    private Unit $unit;

    public function __construct(float $value, Unit $unit) {
        $this->value = $value;
        $this->unit = $unit;
    }

    public function convert() {
        return $this->unit->convert($this->value);
    }

    public function getValue() {
        return $this->value;
    }

    public function getUnit() {
        return $this->unit;
    }
}