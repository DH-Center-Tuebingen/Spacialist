<?php

namespace App\AttributeTypes\Units\Unit;

class Unit {

    private string $label;
    private string $symbol;
    private $conversion;

    public function __construct(string $label, string $symbol, callable $conversion) {
        $this->label = $label;
        $this->symbol = $symbol;
        $this->conversion = $conversion;
    }

    public function getLabel() {
        return $this->label;
    }

    public function getSymbol() {
        return $this->symbol;
    }

    public function is($value) {
        return $this->normalize($value);
    }

    public function normalize($value) {
        return ($this->conversion)($value);
    }

    public function toObject() {
        return [
            'label' => $this->label,
            'symbol' => $this->symbol,
        ];
    }
}
