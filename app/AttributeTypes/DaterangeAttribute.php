<?php

namespace App\AttributeTypes;

use App\Exceptions\InvalidDataException;
use App\Utils\StringUtils;

class DaterangeAttribute extends AttributeBase
{
    protected static string $type = "daterange";
    protected static bool $inTable = true;
    protected static ?string $field = 'json_val';

    private static function toDate(string $date, string $format = "Y-m-d") : string {
        return date($format, strtotime($date));
    }

    public static function parseImport(int|float|bool|string $data) : mixed {
        $data = StringUtils::useGuard(InvalidDataException::class)($data);        
        $dates = explode(";", $data);
        if(count($dates) != 2) {
            throw InvalidDataException::requiredFormat("START;END", $data);
        }
        
        $start = DateAttribute::parseImport($dates[0]);
        $end = DateAttribute::parseImport($dates[1]);
        
        if($start > $end) {
            throw InvalidDataException::requireBefore($start, $end);
        }

        return json_encode([
            "start" => $start,
            "end" => $end,
        ]);
    }

    public static function unserialize(mixed $data) : mixed {
        return json_encode(
            array_map(function($date) {
                return self::toDate($date);
            }, $data)
        );
    }

    public static function serialize(mixed $data) : mixed {
        return json_decode($data);
    }
}