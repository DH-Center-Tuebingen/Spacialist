<?php

namespace Tests\Unit\Attributes;

use App\AttributeTypes\Units\Implementations\AreaUnits;
use App\AttributeTypes\Units\Implementations\LengthUnits;
use App\AttributeTypes\Units\Implementations\MassUnits;
use App\AttributeTypes\Units\Implementations\TemperatureUnits;
use App\AttributeTypes\Units\Implementations\TimeUnits;
use App\AttributeTypes\Units\Implementations\VolumeUnits;
use Tests\TestCase;


class SiAttributeTest extends TestCase {

    const INACCURACY = 1e-10;

    /// Area
    public function testAreaUnits() {
        $areaUnits = new AreaUnits();
        $this->assertEquals('area', $areaUnits->getName());

        $baseUnit = $areaUnits->getBaseUnit();
        $this->assertNotNull($baseUnit);
        $this->assertEquals('square_metre', $baseUnit->getLabel());
        $this->assertEquals('m²', $baseUnit->getSymbol());
        $this->assertEquals(1, $baseUnit->is(1));

        $centimetre = $areaUnits->get('square_centimetre');
        $this->assertNotNull($centimetre);
        $this->assertEquals(10 ** -4, $centimetre->is(1));
        $this->assertEquals('cm²', $centimetre->getSymbol());

        $kilometre = $areaUnits->get('square_kilometre');
        $this->assertNotNull($kilometre);
        $this->assertEquals(10 ** 6, $kilometre->is(1));
        $this->assertEquals('km²', $kilometre->getSymbol());

        /// Imperial Units
        $squareInch = $areaUnits->get('square_inch');
        $this->assertNotNull($squareInch);
        $this->assertEquals('in²', $squareInch->getSymbol());
        $this->assertEquals(0.00064516, $squareInch->is(1));

        $squareFeet = $areaUnits->get('square_feet');
        $this->assertNotNull($squareFeet);
        $this->assertEquals('ft²', $squareFeet->getSymbol());
        $this->assertEquals(0.09290304, $squareFeet->is(1));

        $squareYard = $areaUnits->get('square_yard');
        $this->assertNotNull($squareYard);
        $this->assertEquals('yd²', $squareYard->getSymbol());
        $this->assertEquals(0.83612736, $squareYard->is(1));

        $acre = $areaUnits->get('acre');
        $this->assertNotNull($acre);
        $this->assertEquals('ac', $acre->getSymbol());
        $this->assertEquals(4046.8564224, $acre->is(1));

        $squareMile = $areaUnits->get('square_mile');
        $this->assertNotNull($squareMile);
        $this->assertEquals('mi²', $squareMile->getSymbol());
        $this->assertEquals(2589988.1103360, $squareMile->is(1));
    }


    /// Length
    public function testLengthUnits() {
        $lengthUnits = new LengthUnits();
        $this->assertEquals('length', $lengthUnits->getName());

        $baseUnit = $lengthUnits->getBaseUnit();
        $this->assertEquals('metre', $baseUnit->getLabel());
        $this->assertEquals('m', $baseUnit->getSymbol());

        $nanometre = $lengthUnits->get('nanometre');
        $this->assertNotNull($nanometre);
        $this->assertEquals(10 ** -9, $nanometre->is(1));
        $this->assertEquals('nm', $nanometre->getSymbol());

        $micrometre = $lengthUnits->get('micrometre');
        $this->assertNotNull($micrometre);
        $this->assertEquals(10 ** -6, $micrometre->is(1));
        $this->assertEquals('µm', $micrometre->getSymbol());

        $millimetre = $lengthUnits->get('millimetre');
        $this->assertNotNull($millimetre);
        $this->assertEquals(10 ** -3, $millimetre->is(1));

        $centimetre = $lengthUnits->get('centimetre');
        $this->assertNotNull($centimetre);
        $this->assertEquals(10 ** -2, $centimetre->is(1));
        $this->assertEquals('cm', $centimetre->getSymbol());

        $kilometre = $lengthUnits->get('kilometre');
        $this->assertNotNull($kilometre);
        $this->assertEquals(1000, $kilometre->is(1));
        $this->assertEquals('km', $kilometre->getSymbol());

        /// Imperial Units

        $inch = $lengthUnits->get('inch');
        $this->assertNotNull($inch);
        $this->assertEquals('inch', $inch->getLabel());
        $this->assertEquals('in', $inch->getSymbol());
        $this->assertEquals(0.0254, $inch->is(1));

        $feet = $lengthUnits->get('feet');
        $this->assertNotNull($feet);
        $this->assertEquals('feet', $feet->getLabel());
        $this->assertEquals('ft', $feet->getSymbol());
        $this->assertEquals(0.3048, $feet->is(1));

        $yard = $lengthUnits->get('yard');
        $this->assertNotNull($yard);
        $this->assertEquals('yard', $yard->getLabel());
        $this->assertEquals('yd', $yard->getSymbol());
        $this->assertEquals(0.9144, $yard->is(1));

        $mile = $lengthUnits->get('mile');
        $this->assertNotNull($mile);
        $this->assertEquals('mile', $mile->getLabel());
        $this->assertEquals('mi', $mile->getSymbol());
        $this->assertEquals(1609.344, $mile->is(1));

        // Others
        $lightYear = $lengthUnits->get('light_year');
        $this->assertNotNull($lightYear);
        $this->assertEquals('light_year', $lightYear->getLabel());
        $this->assertEquals('ly', $lightYear->getSymbol());
        $this->assertEquals(9460730472580800, $lightYear->is(1));
    }


    /// Mass
    public function testMassUnits() {
        $massUnits = new MassUnits();
        $this->assertEquals('mass', $massUnits->getName());

        $baseUnit = $massUnits->getBaseUnit();
        $this->assertNotNull($baseUnit);
        $this->assertEquals('gram', $baseUnit->getLabel());
        $this->assertEquals('g', $baseUnit->getSymbol());

        $milligram = $massUnits->get('milligram');
        $this->assertNotNull($milligram);
        $this->assertEquals(10 ** -3, $milligram->is(1));
        $this->assertEquals('mg', $milligram->getSymbol());


        $kilogram = $massUnits->get('kilogram');
        $this->assertNotNull($kilogram);
        $this->assertEquals(1000, $kilogram->is(1));
        $this->assertEquals('kg', $kilogram->getSymbol());

        $ton = $massUnits->get('ton');
        $this->assertNotNull($ton);
        $this->assertEquals($kilogram->is(1000), $ton->is(1));
        $this->assertEquals('t', $ton->getSymbol());

        /// Imperial Units
        $ounce = $massUnits->get('ounce');
        $this->assertNotNull($ounce);
        $this->assertEquals('oz', $ounce->getSymbol());
        $this->assertEquals(28.349523125, $ounce->is(1));

        $pound = $massUnits->get('pound');
        $this->assertNotNull($pound);
        $this->assertEquals('lb', $pound->getSymbol());
        $this->assertEquals(453.59237, $pound->is(1));
    }

    /// Temperature
    public function testTemperatureUnits() {
        $temperatureUnits = new TemperatureUnits();
        $this->assertEquals('temperature', $temperatureUnits->getName());

        $baseUnit = $temperatureUnits->getBaseUnit();
        $this->assertNotNull($baseUnit);
        $this->assertEquals('kelvin', $baseUnit->getLabel());
        $this->assertEquals('K', $baseUnit->getSymbol());

        $celsius = $temperatureUnits->get('celsius');
        $this->assertNotNull($celsius);
        $this->assertEquals('celsius', $celsius->getLabel());
        $this->assertEquals('°C', $celsius->getSymbol());
        $this->assertEquals(273.15, $celsius->is(0));

        $fahrenheit = $temperatureUnits->get('fahrenheit');
        $this->assertNotNull($fahrenheit);
        $this->assertEquals('fahrenheit', $fahrenheit->getLabel());
        $this->assertEquals('°F', $fahrenheit->getSymbol());
        $this->assertEquals(255.3722222222222, $fahrenheit->is(0));

        $réaumur = $temperatureUnits->get('réaumur');
        $this->assertNotNull($réaumur);
        $this->assertEquals('réaumur', $réaumur->getLabel());
        $this->assertEquals('°Ré', $réaumur->getSymbol());
        $this->assertEquals(273.15, $réaumur->is(0));
        $this->assertEquals(373.15, $réaumur->is(80));
    }

    /// Time
    public function testTimeUnits() {
        $timeUnits =  new TimeUnits();
        $this->assertEquals('time', $timeUnits->getName());

        $baseUnit = $timeUnits->getBaseUnit();
        $this->assertEquals('second', $baseUnit->getLabel());
        $this->assertEquals('s', $baseUnit->getSymbol());

        $minute = $timeUnits->get('minute');
        $this->assertEquals('minute', $minute->getLabel());
        $this->assertEquals('min', $minute->getSymbol());
        $this->assertEquals(60, $minute->is(1));

        $hour = $timeUnits->get('hour');
        $this->assertEquals('hour', $hour->getLabel());
        $this->assertEquals('h', $hour->getSymbol());
        $this->assertEquals($minute->is(60), $hour->is(1));

        $day = $timeUnits->get('day');
        $this->assertEquals('day', $day->getLabel());
        $this->assertEquals('d', $day->getSymbol());
        $this->assertEquals($hour->is(24), $day->is(1));

        $year = $timeUnits->get('year');
        $this->assertEquals('year', $year->getLabel());
        $this->assertEquals('a', $year->getSymbol());
        $this->assertEquals($day->is(365), $year->is(1));
    }


    /// Volume
    public function testVolumeUnits() {
        $volumeUnits = new VolumeUnits();
        $this->assertEquals('volume', $volumeUnits->getName());

        $baseUnit = $volumeUnits->getBaseUnit();
        $this->assertNotNull($baseUnit);
        $this->assertEquals('cubic_metre', $baseUnit->getLabel());
        $this->assertEquals('m³', $baseUnit->getSymbol());

        $millilitre = $volumeUnits->get('millilitre');
        $this->assertNotNull($millilitre);
        $this->assertEquals(10 ** -6, $millilitre->is(1));
        $this->assertEquals('ml', $millilitre->getSymbol());

        $litre = $volumeUnits->get('litre');
        $this->assertNotNull($litre);
        $this->assertEquals(10 ** -3, $litre->is(1));
        $this->assertEquals('l', $litre->getSymbol());

        // Imperial Units        
        $fluidOunce = $volumeUnits->get('fluid_ounce_us');
        $this->assertNotNull($fluidOunce);
        $this->assertEquals('fl oz', $fluidOunce->getSymbol());
        $this->assertEquals(2.95735295625e-5, $fluidOunce->is(1));
        $this->assertEqualsWithDelta($millilitre->is(29.5735295625), $fluidOunce->is(1), self::INACCURACY);

        $pint = $volumeUnits->get('pint_us');
        $this->assertNotNull($pint);
        $this->assertEquals('pt', $pint->getSymbol());
        $this->assertEqualsWithDelta($millilitre->is(473.176473), $pint->is(1), self::INACCURACY);

        $gallon = $volumeUnits->get('gallon_us');
        $this->assertNotNull($gallon);
        $this->assertEquals('gal', $gallon->getSymbol());
        $this->assertEqualsWithDelta($litre->is(3.785411784), $gallon->is(1), self::INACCURACY);

        // Imperal Conversions
        $this->assertEqualsWithDelta($pint->is(1), $gallon->is(1 / 8), self::INACCURACY);
        $this->assertEqualsWithDelta($pint->is(1), $fluidOunce->is(16), self::INACCURACY);


        $cubicMile = $volumeUnits->get('cubic_mile');
        $this->assertNotNull($cubicMile);
        $this->assertEquals('mi³', $cubicMile->getSymbol());
        $this->assertEqualsWithDelta($baseUnit->is(4168181825.44058), $cubicMile->is(1), self::INACCURACY);
    }
}
