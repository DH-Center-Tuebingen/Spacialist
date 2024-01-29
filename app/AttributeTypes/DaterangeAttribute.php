<?php

namespace App\AttributeTypes;

use App\Exceptions\InvalidDataException;

class DaterangeAttribute extends AttributeBase
{
    protected static string $type = "daterange";
    protected static bool $inTable = true;
    protected static ?string $field = 'json_val';

    private static function toDate(string $date, string $format = "Y-m-d") : string {
        return date($format, strtotime($date));
    }

    public static function fromImport(string $data) : mixed {
        $dates = explode(";", $data);

        if(count($dates) != 2) {
            throw new InvalidDataException("Given data does not match this datatype's format (START;END)");
        }

        return [
            "start" => trim(self::toDate($dates[0])),
            "end" => trim(self::toDate($dates[1])),
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
