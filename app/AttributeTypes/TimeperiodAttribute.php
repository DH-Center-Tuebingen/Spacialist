<?php

namespace App\AttributeTypes;

class TimeperiodAttribute extends AttributeBase
{
    protected static string $type = "timeperiod";
    protected static bool $inTable = true;
    protected static ?string $field = 'json_val';

    public static function fromImport(int|float|bool|string $data) : mixed {
        $startLabel = 'ad';
        $endLabel = 'ad';
        $parts = explode(';', $data);

        if(count($parts) != 2) {
            throw new InvalidDataException("Given data does not match this datatype's format (START;END)");
        }

        $start = intval(trim($parts[0]));
        $end = intval(trim($parts[1]));

        if($end < $start) {
            throw new InvalidDataException("Start date must not be after end data ($start, $end)");
        }

        if($start < 0) {
            $startLabel = 'bc';
            $start = abs($start);
        }
        if($end < 0) {
            $endLabel = 'bc';
            $end = abs($end);
        }
        return json_encode([
            'start' => $start,
            'startLabel' => $startLabel,
            'end' => $end,
            'endLabel' => $endLabel,
        ]);
    }

    public static function unserialize(mixed $data) : mixed {
        return EpochAttribute::unserialize($data);
    }

    public static function serialize(mixed $data) : mixed {
        return json_decode($data);
    }
}
