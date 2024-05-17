<?php 

namespace App\AttributeTypes\Units\SiPrefix;

class SiPrefix {

    private $label;
    private $symbol;
    private $power;

    function __construct($label, $symbol, $power) {
        $this->label = $label;
        $this->symbol = $symbol;
        $this->power = $power;
    }

    public function getLabel() {
        return $this->label;
    }

    public function getSymbol() {
        return $this->symbol;
    }

    public function getPower() {
        return $this->power;
    }
}