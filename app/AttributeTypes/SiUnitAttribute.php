<?php

namespace App\AttributeTypes;

use App\AttributeTypes\Units\Implementations\UnitManager;
use App\Exceptions\InvalidDataException;


class SiUnitAttribute extends AttributeBase
{
    protected static string $type = "si-unit";
    protected static bool $inTable = true;
    protected static ?string $field = 'json_val';


    public static function getGlobalData() : array {
        $unitsArray = [];
        foreach(UnitManager::get()->getUnitSystems() as $unitSystem) {
            $unitsArray[$unitSystem->name] = $unitSystem->toArray();
        }

        return $unitsArray;
    }

    public static function fromImport(int|float|bool|string $data) : mixed {
        $parts = explode(';', $data);

        if(count($parts) != 2) {
            throw new InvalidDataException("Given data does not match this datatype's format (VALUE;UNIT)");
        }

        $value = trim($parts[0]);
        if(!is_numeric($value)) {
            throw new InvalidDataException("Given data is not a numeric value!");
        }
        $value = floatval($value);

        $unit = trim($parts[1]);
        $unitFound = UnitManager::get()->findUnitByAny($unit);
        
        if(!isset($unitFound)) {
            throw new InvalidDataException("Given data is not a valid unit!");
        }

        return json_encode([
            'value' => $value,
            'unit' => $unitFound->getSymbol(),
            'normalized' => $unitFound->normalize($value),
        ]);
    }

    public static function unserialize(mixed $data) : mixed {
        $unit = UnitManager::get()->findUnitByLabel($data['unit']);
        $data['normalized'] =  $unit->normalize($data['value']);
        return json_encode($data);
    }

    public static function serialize(mixed $data) : mixed {
        return json_decode($data);
    }
}
