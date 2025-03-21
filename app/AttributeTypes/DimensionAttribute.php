<?php

namespace App\AttributeTypes;

use App\Exceptions\InvalidDataException;
use App\Utils\StringUtils;

class DimensionAttribute extends AttributeBase
{
    protected static string $type = "dimension";
    protected static bool $inTable = false;
    protected static ?string $field = 'json_val';

    public static function parseImport(int|float|bool|string $data) : mixed {
        $data = StringUtils::useGuard(InvalidDataException::class)($data);
        $parts = explode(';', $data);

        $format = "VAL1;VAL2;VAL3;UNIT";
        if(count($parts) != 4) {
            throw InvalidDataException::requiredFormat($format, $data);
        }

        for($i = 0; $i < 3; $i++) {
            if(!is_numeric($parts[$i])) {
                throw InvalidDataException::requiredFormat($format, $data);
            }
        }

        return json_encode([
            'B' => floatval(trim($parts[0])),
            'H' => floatval(trim($parts[1])),
            'T' => floatval(trim($parts[2])),
            'unit' => trim($parts[3]),
        ]);
    }

    public static function parseExport(mixed $data) : string {
        $dataAsObj = json_decode($data);
        return ($dataAsObj->B ?? '') . ';' . ($dataAsObj->H ?? '') . ';' . ($dataAsObj->T ?? '') . ';' . ($dataAsObj->unit ?? '');
    }

    public static function unserialize(mixed $data) : mixed {
        return json_encode($data);
    }

    public static function serialize(mixed $data) : mixed {
        return json_decode($data);
    }
}
