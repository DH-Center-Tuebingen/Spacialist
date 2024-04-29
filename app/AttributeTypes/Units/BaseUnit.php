<?php

namespace App\AttributeTypes\Units;

class BaseUnit {

    private string $label;
    private string $symbol;

    public function __construct(string $label, string $symbol) {
        $this->label = $label;
        $this->symbol = $symbol;
    }
    public function getLabel() {
        return $this->label;
    }

    public function getSymbol() {
        return $this->symbol;
    }

    public function convert($value) {
        return $value;
    }

}