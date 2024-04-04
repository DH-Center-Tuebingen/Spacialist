<?php

namespace App\AttributeTypes;

use App\Exceptions\InvalidDataException;

class SiUnitAttribute extends AttributeBase
{
    protected static string $type = "si-unit";
    protected static bool $inTable = true;
    protected static ?string $field = 'json_val';

    public static function getUnits() {
        return [
            'time' => [
                'default' => 's',
                'units' => [
                    [
                        'symbol' => 's',
                        'convert' => fn($v) => $v,
                        'label' => 'second',
                    ],
                    [
                        'symbol' => 'm',
                        'convert' => fn($v) => $v * 60,
                        'label' => 'minute',
                    ],
                    [
                        'symbol' => 'h',
                        'convert' => fn($v) => $v * 3600, // 60 * 60
                        'label' => 'hour',
                    ],
                    [
                        'symbol' => 'd',
                        'convert' => fn($v) => $v * 86400, // 60 * 60 * 24
                        'label' => 'day',
                    ],
                    [
                        'symbol' => 'a',
                        'convert' => fn($v) => $v * 31536000, // 60 * 60 * 24 * 365
                        'label' => 'year',
                    ],
                ],
            ],
            'length' => [
                'default' => 'm',
                'units' => [
                    [
                        'symbol' => 'nm',
                        'convert' => fn($v) => $v * pow(10, -9),
                        'label' => 'nanometre',
                    ],
                    [
                        'symbol' => 'µm',
                        'convert' => fn($v) => $v * pow(10, -6),
                        'label' => 'micrometre',
                    ],
                    [
                        'symbol' => 'mm',
                        'convert' => fn($v) => $v * pow(10, -3),
                        'label' => 'millimetre',
                    ],
                    [
                        'symbol' => 'cm',
                        'convert' => fn($v) => $v * pow(10, -2),
                        'label' => 'centimetre',
                    ],
                    [
                        'symbol' => 'dm',
                        'convert' => fn($v) => $v * pow(10, -1),
                        'label' => 'decimetre',
                    ],
                    [
                        'symbol' => 'm',
                        'convert' => fn($v) => $v,
                        'label' => 'metre',
                    ],
                    [
                        'symbol' => 'dam',
                        'convert' => fn($v) => $v * 10,
                        'label' => 'decametre',
                    ],
                    [
                        'symbol' => 'hm',
                        'convert' => fn($v) => $v * pow(10, 2),
                        'label' => 'hectometre',
                    ],
                    [
                        'symbol' => 'km',
                        'convert' => fn($v) => $v * pow(10, 3),
                        'label' => 'kilometre',
                    ],
                    [
                        'symbol' => 'in',
                        'convert' => fn($v) => $v * 0.0254,
                        'label' => 'inch',
                    ],
                    [
                        'symbol' => 'ft',
                        'convert' => fn($v) => $v * 0.3048,
                        'label' => 'feet',
                    ],
                    [
                        'symbol' => 'yd',
                        'convert' => fn($v) => $v * 0.9144,
                        'label' => 'yard',
                    ],
                    [
                        'symbol' => 'mi',
                        'convert' => fn($v) => $v * 1609.3440000009,
                        'label' => 'mile',
                    ],
                ],
            ],
            'area' => [
                'default' => 'm²',
                'units' => [
                    [
                        'symbol' => ['m²', 'qm', 'sm'],
                        'convert' => fn($v) => $v,
                        'label' => 'square_metre',
                    ],
                    [
                        'symbol' => ['km²', 'qkm', 'skm'],
                        'convert' => fn($v) => $v * pow(10, 6),
                        'label' => 'square_kilometre',
                    ],
                    [
                        'symbol' => 'in²',
                        'convert' => fn($v) => $v * pow(0.0254, 2),
                        'label' => 'square_inch',
                    ],
                    [
                        'symbol' => ['ft²', 'qft', 'sft'],
                        'convert' => fn($v) => $v * pow(0.3048, 2),
                        'label' => 'square_feet',
                    ],
                    [
                        'symbol' => ['yd²', 'qyd', 'syd'],
                        'convert' => fn($v) => $v * pow(0.9144, 2),
                        'label' => 'square_yard',
                    ],
                    [
                        'symbol' => ['ac'],
                        'convert' => fn($v) => $v * 4046.8564224,
                        'label' => 'acre',
                    ],
                    [
                        'symbol' => ['mi²', 'qmi', 'smi'],
                        'convert' => fn($v) => $v * pow(1609.3440000009, 2),
                        'label' => 'square_mile',
                    ],
                ],
            ],
            'volume' => [
                'default' => 'm³',
                'units' => [
                    [
                        'symbol' => 'cm³',
                        'convert' => fn($v) => $v * pow(10, -6),
                        'label' => 'cubic_centimetre',
                    ],
                    [
                        'symbol' => ['dm³', 'l'],
                        'convert' => fn($v) => $v * pow(10, -3),
                        'label' => 'cubic_decimetre',
                    ],
                    [
                        'symbol' => 'm³',
                        'convert' => fn($v) => $v,
                        'label' => 'cubic_metre',
                    ],
                    [
                        'symbol' => 'km³',
                        'convert' => fn($v) => $v * pow(10, 9),
                        'label' => 'cubic_kilometre',
                    ],
                    [
                        'symbol' => 'fl oz',
                        'convert' => fn($v) => $v * 0.000029573529651571238,
                        'label' => 'fluid_ounce_us',
                    ],
                    [
                        'symbol' => 'pt',
                        'convert' => fn($v) => $v * 0.000473176,
                        'label' => 'pint_us',
                    ],
                    [
                        'symbol' => 'c',
                        'convert' => fn($v) => $v * 0.0002365882372125699,
                        'label' => 'cup_us',
                    ],
                    [
                        'symbol' => 'gal',
                        'convert' => fn($v) => $v * 0.003785412,
                        'label' => 'gallon_us',
                    ],
                    [
                        'symbol' => 'mi³',
                        'convert' => fn($v) => $v * pow(1609.3440000009, 3),
                        'label' => 'cubic_mile',
                    ],
                ],
            ],
            'mass' => [
                'default' => 'kg',
                'units' => [
                    [
                        'symbol' => 'g',
                        'convert' => fn($v) => $v * pow(10, -3),
                        'label' => 'gram',
                    ],
                    [
                        'symbol' => 'kg',
                        'convert' => fn($v) => $v,
                        'label' => 'kilogram',
                    ],
                    [
                        'symbol' => 't',
                        'convert' => fn($v) => $v * pow(10, 3),
                        'label' => 'ton',
                    ],
                    [
                        'symbol' => 'oz',
                        'convert' => fn($v) => $v * 0.028349523125,
                        'label' => 'ounce',
                    ],
                    [
                        'symbol' => 'lb',
                        'convert' => fn($v) => $v * 0.45359237,
                        'label' => 'pound',
                    ],
                ],
            ],
            'temperature' => [
                // K, C, F, R?
                'default' => '°C',
                'units' => [
                    [
                        'symbol' => '°C',
                        'convert' => fn($v) => $v,
                        'label' => 'degree_celsius',
                    ],
                    [
                        'symbol' => '°K',
                        'convert' => fn($v) => $v - 273,15,
                        'label' => 'degree_kelvin',
                    ],
                    [
                        'symbol' => '°F',
                        'convert' => fn($v) => ($v - 32) / 1,8,
                        'label' => 'degree_fahrenheit',
                    ],
                    [
                        'symbol' => '°R',
                        'convert' => fn($v) => $v * 1.25,
                        'label' => 'degree_réaumur',
                    ],
                ],
            ],
            // 'substance' => [],
            // 'luminous' => [],
            // 'energy' => [],
            // 'discspace' => [
            //     // b, kb, mb, gb, tb, pb
            //     // bi, kibi, mibi, gibi, tibi, pibi
            // ],
        ];
    }

    private static function getUnitFromKey(string $value, string $key) : array|null {
        if(!isset($key) || !isset($value)) return null;

        foreach(self::getUnits() as $grpLabel => $grp) {
            foreach($grp['units'] as $unit) {
                if(is_array($unit[$key])) {
                    if(in_array($value, $unit[$key])) {
                        return $unit;
                    }
                } else {
                    if($unit[$key] == $value) {
                        return $unit;
                    }
                }
            }
        }

        return null;
    }

    private static function getUnitFromLabel(string $label) : array|null {
        return self::getUnitFromKey($label, 'label');
    }

    private static function getUnitFromSymbol(string $symbol) : array|null {
        return self::getUnitFromKey($symbol, 'symbol');
    }

    private static function getUnitFromUnknown(string $value) : array|null {
        $unit = self::getUnitFromLabel($value);
        if(isset($unit)) {
            return $unit;
        }

        $unit = self::getUnitFromSymbol($value);

        if(isset($unit)) {
            return $unit;
        }

        return null;
    }

    private static function normalizeValue(mixed $value, array|string $unit) : mixed {
        if(!is_array($unit)) {
            $unit = self::getUnitFromUnknown($unit);
        }

        return $unit['convert']($value);
    }

    public static function addGlobalData() : array {
        $stripUnits = [];
        foreach(self::getUnits() as $grpLabel => $grp) {
            $grpArr = [];
            $grpArr['default'] = $grp['default'];
            $grpArr['units'] = [];
            foreach($grp['units'] as $unit) {
                $grpArr['units'][] = [
                    'symbol' => $unit['symbol'],
                    'label' => $unit['label'],
                ];
            }
            $stripUnits[$grpLabel] = $grpArr;
        }

        return $stripUnits;
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
        $unitFound = self::getUnitFromUnknown($unit);
        
        if(!isset($unitFound)) {
            throw new InvalidDataException("Given data is not a valid unit!");
        }

        $norm = self::normalizeValue($value, $unitFound);

        return json_encode([
            'value' => $value,
            'unit' => $unit,
            'normalized' => $norm,
        ]);
    }

    public static function unserialize(mixed $data) : mixed {
        $unit = self::getUnitFromLabel($data['unit']);
        $norm = self::normalizeValue($data['value'], $unit);
        $data['normalized'] = $norm;
        return json_encode($data);
    }

    public static function serialize(mixed $data) : mixed {
        return json_decode($data);
    }
}
