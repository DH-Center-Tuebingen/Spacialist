<?php

namespace App\AttributeTypes\Units\Unit;

use App\AttributeTypes\Units\Call;
use App\AttributeTypes\Units\SiPrefix\PowerPrefix;
use App\AttributeTypes\Units\SiPrefix\SiPrefix;

class SiUnit extends Unit {

    private SiPrefix $prefix;
    private BaseUnit $baseUnit;
    private Int $dimension;

    public function __construct(SiPrefix $prefix, BaseUnit $baseUnit, $dimension = 1) {
        $this->prefix = $prefix;
        $this->baseUnit = $baseUnit;
        $this->dimension = $dimension;

        $label = $this->constructLabel();
        $symbol = $this->constructSymbol();

        parent::__construct($label, $symbol, $this->constructConversion());
    }

    private function constructLabel() {
        return $this->prefix->getLabel() . $this->baseUnit->getLabel();
    }

    private function constructSymbol() {
        return $this->prefix->getSymbol() . $this->baseUnit->getSymbol();
    }

    public function constructConversion() {
        return Call::si($this->prefix, $this->dimension);
    }
}
