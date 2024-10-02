<?php

namespace App\AttributeTypes;

use App\Exceptions\InvalidDataException;
use App\Utils\StringUtils;
use Exception;

class DaterangeAttribute extends AttributeBase
{
    protected static string $type = "daterange";
    protected static bool $inTable = true;
    protected static ?string $field = 'json_val';

    private static function toDate(string $date, string $format = "Y-m-d") : string {
        return date($format, strtotime($date));
    }

    public static function fromImport(int|float|bool|string $data) : mixed {
        $errormsg = "Given data does not match this datatype's format: START;END";
        try{
            $data = StringUtils::guard($data);
        } catch(Exception $e) {
            throw new InvalidDataException($errormsg);
        }
        if($data === "") {
            return null;
        }
        
        $dates = explode(";", $data);

        if(count($dates) != 2) {
            throw new InvalidDataException($errormsg);
        }
        
        $start = self::toDate(trim($dates[0]));
        $end = self::toDate(trim($dates[1]));
        
        if($start > $end) {
            throw new InvalidDataException("End date cannot be before start date.");
        }

        return [
            "start" => $start,
            "end" => $end,
        ];
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
