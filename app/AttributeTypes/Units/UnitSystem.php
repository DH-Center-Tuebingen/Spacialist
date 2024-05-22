<?php

namespace App\AttributeTypes\Units;

use App\AttributeTypes\Units\Unit;

use Exception;

class UnitSystem {

    public $name;
    private $units = [];
    private $labelMap = [];
    private $symbolMap = [];
    private $baseIndex = null;

    public function __construct(string $name, array $units) {
        $this->name = $name;
        $baseFound = false;
        foreach($units as $unit) {
            $position = $this->add($unit);
            if($unit->getBase()) {
                if($baseFound) {
                    throw new Exception('Base already defined for this Unit System');
                }
                $baseFound = true;
                $this->baseIndex = $position - 1;
            }
        }

        if(!$baseFound) {
            throw new Exception('No Base defined for this Unit System');
        }
    }

    public function addMultiple(array $units) {
        foreach ($units as $unit) {
            if($unit->getBase()) {
                throw new Exception('Base already defined for this Unit System');
            }
            $this->add($unit);
        }
    }

    public function add(Unit $unit) {
        if (isset($this->labelMap[$unit->getLabel()]) || isset($this->symbolMap[$unit->getSymbol()])) {
            throw new Exception("Unit already exists: {$unit->getLabel()} ({$unit->getSymbol()})");
        }

        if($unit->getBase() && isset($this->baseIndex)) {
            throw new Exception('Base already defined for this Unit System');
        }

        $this->labelMap[$unit->getLabel()] = $unit;
        $this->symbolMap[$unit->getSymbol()] = $unit;
        $this->units[] = $unit;
        return count($this->units);
    }

    public function get(string $label): ?Unit {
        return $this->getByLabel($label);
    }

    public function getByLabel(string $label): ?Unit {

        if (!isset($this->labelMap[$label])) return null;

        return $this->labelMap[$label];
    }

    public function getBySymbol(string $symbol): ?Unit {
        if (!isset($this->symbolMap[$symbol])) return null;

        return $this->symbolMap[$symbol];
    }

    public function getBaseUnit() {
        return $this->units[$this->baseIndex];
    }

    public function getName() {
        return $this->name;
    }

    public function getUnits() {
        return $this->units;
    }

    public function toArray() {
        return [
            'default' => $this->getBaseUnit()->getSymbol(), // Shouldn't we better use the label here?
            'units' => array_map(function ($unit) {
                return $unit->toObject();
            }, $this->units),
        ];
    }
}
