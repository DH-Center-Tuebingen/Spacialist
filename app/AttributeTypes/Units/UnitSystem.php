<?php

namespace App\AttributeTypes\Units;

use App\AttributeTypes\Units\Unit\BaseUnit;
use App\AttributeTypes\Units\Unit\Unit;;
use Exception;

class UnitSystem {

    public $name;
    private $units = [];
    private $labelMap = [];
    private $symbolMap = [];

    public function __construct(string $name, BaseUnit $baseUnit) {
        $this->name = $name;
        $this->add($baseUnit);
    }

    public function addMultiple(array $units) {
        foreach ($units as $unit) {
            $this->add($unit);
        }
    }

    public function add(Unit $unit) {
        if (isset($this->labelMap[$unit->getLabel()]) || isset($this->symbolMap[$unit->getSymbol()])) {
            throw new Exception('Unit already exists');
        }

        $this->labelMap[$unit->getLabel()] = $unit;
        $this->symbolMap[$unit->getSymbol()] = $unit;
        $this->units[] = $unit;
    }

    public function getByLabel(string $label): ?Unit {

        if(!isset($this->labelMap[$label])) return null;

        return $this->labelMap[$label];
    }

    public function getBySymbol(string $symbol): ?Unit {
        if(!isset($this->symbolMap[$symbol])) return null;

        return $this->symbolMap[$symbol];
    }

    public function getBaseUnit() {
        return $this->units[0];
    }

    public function getName(){
        return $this->name;
    }

    public function getUnits() {
        return $this->units;
    }

    public function toArray(){
        $array = [];
        $array['default'] = $this->getBaseUnit()->getSymbol(); // Shouldn't we better use the label here?
        $array['units'] = array_map(function($unit) {
            return $unit->toObject();
        }, $this->units);
        return $array;
    }
}
