<?php

namespace App\AttributeTypes;

use App\AttributeTypes\Units\Implementations\UnitManager;
use App\Exceptions\InvalidDataException;
use App\Utils\StringUtils;


class SiUnitAttribute extends AttributeBase {
    protected static string $type = "si-unit";
    protected static bool $inTable = true;
    protected static ?string $field = 'json_val';

    public static function getGlobalData(): array {
        $unitsArray = [];
        foreach(UnitManager::get()->getUnitSystems() as $unitSystem) {
            $unitsArray[$unitSystem->name] = $unitSystem->toArray();
        }

        return $unitsArray;
    }

    public static function parseImport(int|float|bool|string $data): mixed {
        $data = StringUtils::useGuard(InvalidDataException::class, __('validation.string', ['attribute' => __('dictionary.value')]))($data);
        $parts = explode(';', $data);

        if(count($parts) != 2) {
            $format = __('dictionary.value') . ';' . __('dictionary.unit');
            throw new InvalidDataException(__('validation.import_format', ['format' => $format]));
        }

        $value = trim($parts[0]);
        if(!is_numeric($value)) {
            $section_1 = __('dictionary.section') . ' 1';
            throw InvalidDataException::requireNumeric($section_1 . ' => ' . $value);
        }

        $value = floatval($value);
        $unit = trim($parts[1]);
        $unitFound = UnitManager::get()->findUnitByAny($unit);

        if(!isset($unitFound)) {
            $section_2 = __('dictionary.section') . ' 2';
            throw InvalidDataException::invalidUnit($section_2 . ' => ' . $unit);
        }

        return json_encode([
            'value' => $value,
            'unit' => $unitFound->getSymbol(),
            'normalized' => $unitFound->normalize($value),
        ]);
    }

    public static function parseExport(mixed $data) : string {
        $dataAsObj = json_decode($data);
        return $dataAsObj->value . ';' . $dataAsObj->unit;
    }

    public static function unserialize(mixed $data): mixed {
        if(isset($data["unit"])) {
            $unit = UnitManager::get()->findUnitByAny($data['unit']);
        }

        if(!isset($unit) || !isset($data["value"]) || !isset($data["unit"])) {
            throw InvalidDataException::invalidUnit(__('dictionary.value'));
        }

        $data['normalized'] =  $unit->normalize($data['value']);
        return json_encode($data);
    }

    public static function serialize(mixed $data): mixed {
        return json_decode($data);
    }
}
