<?php

namespace App\AttributeTypes\Units\Implementations;

use App\AttributeTypes\Units\Unit;
use App\AttributeTypes\Units\UnitSystem;
use App\Patterns\Singleton;
use Exception;

class UnitManager extends Singleton {

    private array $unitSystems = [];

    function __construct() {
        parent::__construct();
        $this->addAllUnitSystems();
    }

    public function addAllUnitSystems(): void {
        $this->addUnitSystems([
            new AreaUnits(),
            new ForceUnits(),
            new LengthUnits(),
            new MassUnits(),
            new PressureUnits(),
            new SpeedUnits(),
            new TemperatureUnits(),
            new TimeUnits(),
            new VolumeUnits(),
        ]);
    }

    public function addUnitSystems(array $unitSystems): void {
        foreach($unitSystems as $unitSystem) {
            $this->addUnitSystem($unitSystem);
        }
    }

    public function addUnitSystem(UnitSystem $system): void {
        foreach($system->getUnits() as $unit) {
            $this->verifyUnitDoesNotExist($system, $unit);
        }

        $this->unitSystems[] = $system;
    }

    /**
     * Verifies that the unit does not already exist in the unit systems.
     * The unit systems are rigidly set beforehand and should be validated on startup.
     * If duplicates are being added, an error is thrown. 
     * 
     * @param Unit $unit
     * @throws Exception
     */
    private function verifyUnitDoesNotExist(unitSystem $unitSystem, Unit $unit): void {

        $unityByLabel = $this->findUnitByLabel($unit->getLabel());
        if(isset($unityByLabel)) {
            throw new Exception('Error on "' . $unitSystem->getName() . '": Unit with Label "' . $unit->getLabel() . '" already exists');
        }

        $unitBySymbol = $this->findUnitBySymbol($unit->getSymbol());
        if(isset($unitBySymbol)) {
            throw new Exception('Error on "' . $unitSystem->getName() . '": Unit with Symbol "' . $unit->getSymbol() . '" already exists');
        }
    }

    function findUnitByAny(string $text): ?Unit {
        $unit = $this->findUnitByLabel($text);
        if(isset($unit)) {
            return $unit;
        }
        $unit = $this->findUnitBySymbol($text);
        if(isset($unit)) {
            return $unit;
        }

        return null;
    }

    function findUnitByLabel(string $label): ?Unit {
        return $this->getByUnitSystemFunc($label, 'getByLabel');
    }

    function findUnitBySymbol(string $symbol): ?Unit {
        return $this->getByUnitSystemFunc($symbol, 'getBySymbol');
    }

    private function getByUnitSystemFunc(string $value, string $functionName): ?Unit {
        foreach($this->unitSystems as $unitSystem) {
            $unit = $unitSystem->{$functionName}($value);
            if(isset($unit)) {
                return $unit;
            }
        }
        return null;
    }

    public function getUnitSystem(string $name): ?UnitSystem {
        foreach($this->unitSystems as $unitSystem) {
            if($unitSystem->getName() == $name) {
                return $unitSystem;
            }
        }
        return null;
    }

    public function getUnitSystems(): array {
        return $this->unitSystems;
    }

    public function hasQuantity(string $quantity): bool {
        foreach($this->unitSystems as $unitSystem) {
            if($unitSystem->getName() == $quantity) {
                return true;
            }
        }
        return false;
    }
}
