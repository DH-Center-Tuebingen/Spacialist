<?php

namespace App\AttributeTypes\Units;

use Exception;

class UnitSystem
{

    public $name;
    private $units = [];
    private $labelMap = [];
    private $symbolMap = [];

    public function __construct(string $name, Unit $baseUnit = null)
    {
        $this->name = $name;
        if ($baseUnit) {
            $this->add($baseUnit);
        }
    }

    public function add(Unit $unit)
    {
        if (isset($this->labelMap[$unit->getLabel()]) || isset($this->symbolMap[$unit->getSymbol()])) {
            throw new Exception('Unit already exists');
        }

        $this->labelMap[$unit->getLabel()] = $unit;
        $this->symbolMap[$unit->getSymbol()] = $unit;
        $this->units[] = $unit;
    }

    public function getByLabel(string $label) : Unit
    {
        return $this->labelMap[$label];
    }

    public function getBySymbol(string $symbol) : Unit
    {
        return $this->symbolMap[$symbol];
    }

    public function getBaseUnit()
    {
        return $this->units[0];
    }
}
