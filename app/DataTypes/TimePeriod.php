<?php

namespace App\DataTypes;

use JsonSerializable;

class TimePeriod implements JsonSerializable {
    private int $start;
    private int $end;

    public function __construct(int $start, int $end) {
        $this->start = $start;
        $this->end = $end;
    }

    public function getStart() : int {
        return $this->start;
    }

    public function getEnd() : int {
        return $this->end;
    }


    public function __toString() : string {
        return $this->start . ' -> ' . $this->end;
    }

    public function determineLabel(int $year) : string {
        return $year > 0 ? 'ad' : 'bc';
    }

    public function jsonSerialize() : array {
        return [
            'start' => abs($this->start),
            'startLabel' => $this->determineLabel($this->start),
            'end' => abs($this->end),
            'endLabel' => $this->determineLabel($this->end),
        ];
    }
}