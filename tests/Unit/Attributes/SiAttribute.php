<?php

namespace Tests\Unit\Attributes;

use App\AttributeTypes\Units\TimeUnits;
use Tests\TestCase;


class CommandTest extends TestCase
{
    /// Time
    public function testTimeUnits()
    {
        $timeUnits = new TimeUnits();

        $baseUnit = $timeUnits->getBaseUnit();
        $this->assertEquals('second', $baseUnit->getLabel());
        $this->assertEquals('s', $baseUnit->getSymbol());

        $minute = $timeUnits->getByLabel('minute');
        $this->assertEquals('minute', $minute->getLabel());
        $this->assertEquals('min', $minute->getSymbol());
        $this->assertEquals(60, $minute->normalize(1));

        $hour = $timeUnits->getByLabel('hour');
        $this->assertEquals('hour', $hour->getLabel());
        $this->assertEquals('h', $hour->getSymbol());
        $this->assertEquals($minute->normalize(60), $hour->normalize(1));

        $day = $timeUnits->getByLabel('day');
        $this->assertEquals('day', $day->getLabel());
        $this->assertEquals('d', $day->getSymbol());
        $this->assertEquals($hour->normalize(24), $day->normalize(1));

        $year = $timeUnits->getByLabel('year');
        $this->assertEquals('year', $year->getLabel());
        $this->assertEquals('a', $year->getSymbol());
        $this->assertEquals($day->normalize(365), $year->normalize(1));
    }
}
