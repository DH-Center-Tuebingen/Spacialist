<?php

namespace App\AttributeTypes\Units;

enum UnitType {
    case SI;
    case DEFAULT;
};

class Unit {

    private string $label;
    private string $symbol;
    private bool $base;
    private $conversion;

    public function __construct(string $label, string $symbol, callable $conversion, bool $base = false) {
        $this->label = $label;
        $this->symbol = $symbol;
        $this->conversion = $conversion;
        $this->base = $base;
    }

    public static function createBase(string $label, string $symbol) {
        $conversion = Call::identity();
        return new self($label, $symbol, $conversion, true);
    }

    public static function createUnit(string $label, string $symbol, float $factor, int $dimension = 1, UnitType $type = UnitType::DEFAULT) {
        if($type == UnitType::SI) {
            $conversion = Call::si($factor, $dimension);
        } else {
            $conversion = Call::multiply($factor, $dimension);
        }
        return new self($label, $symbol, $conversion);
    }

    public static function createWith(string $label, string $symbol, callable $conversion) {
        return new self($label, $symbol, $conversion);
    }

    public function getLabel() {
        return $this->label;
    }

    public function getSymbol() {
        return $this->symbol;
    }

    public function getBase() {
        return $this->base;
    }

    // redundant??
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
