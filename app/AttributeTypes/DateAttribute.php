<?php

namespace App\AttributeTypes;

use App\Exceptions\InvalidDataException;
use App\Utils\StringUtils;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;

class DateAttribute extends AttributeBase
{
    protected static string $type = "date";
    protected static bool $inTable = true;
    protected static ?string $field = 'dt_val';

    private static string $format = "Y-m-d";

    public static function parseImport(int|float|bool|string $data) : mixed {
        $data = StringUtils::useGuard(InvalidDataException::class)($data);

        $errmsg = "Your provided date ($data) does not match the required format of 'Y-m-d'";

        // have to do hasFormat and createFromFormat, because both allow dates the other does not
        // e.g. 20222 is a valid year for hasFormat, but not createFromFormat
        // on the other hand 13 is a valid month for createFromFormat (overflows to next year's january), but not hasFormat
        if(!Carbon::hasFormat($data, self::$format)) {
            throw new InvalidDataException($errmsg);
        }

        try {
            $date = Carbon::createFromFormat(self::$format, $data);
            return $date->format(self::$format);
        } catch(InvalidFormatException $e) {
            throw new InvalidDataException($errmsg);
        }
    }

    public static function unserialize(mixed $data) : mixed {
        return $data;
    }

    public static function serialize(mixed $data) : mixed {
        return $data;
    }
}
