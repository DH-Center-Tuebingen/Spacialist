<?php

namespace App\AttributeTypes\Units;

class Unit extends BaseUnit {

    private $conversion;

    public function __construct(string $label, string $symbol, callable $conversion)  {
        parent::__construct($label, $symbol);
        $this->conversion = $conversion;
    }

    public function convert($value) {
        return ($this->conversion)($value);
    }


}