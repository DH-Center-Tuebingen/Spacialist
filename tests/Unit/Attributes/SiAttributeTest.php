<?php

use App\AttributeTypes\AttributeBase;
use App\AttributeTypes\SiUnitAttribute;
use App\AttributeTypes\Units\Implementations\AreaUnits;
use App\AttributeTypes\Units\Implementations\ForceUnits;
use App\AttributeTypes\Units\Implementations\LengthUnits;
use App\AttributeTypes\Units\Implementations\MassUnits;
use App\AttributeTypes\Units\Implementations\PressureUnits;
use App\AttributeTypes\Units\Implementations\QuotientUnits;
use App\AttributeTypes\Units\Implementations\SpeedUnits;
use App\AttributeTypes\Units\Implementations\TemperatureUnits;
use App\AttributeTypes\Units\Implementations\TimeUnits;
use App\AttributeTypes\Units\Implementations\VolumetricFlowUnits;
use App\AttributeTypes\Units\Implementations\VolumeUnits;
use App\AttributeValue;
use App\Exceptions\InvalidDataException;

const INACCURACY = 1e-10;

test('area units', function () {
    $areaUnits = new AreaUnits();
    expect($areaUnits->getName())->toEqual('area');

    $baseUnit = $areaUnits->getBaseUnit();
    expect($baseUnit)->not->toBeNull();
    expect($baseUnit->getLabel())->toEqual('square_metre');
    expect($baseUnit->getSymbol())->toEqual('m²');
    expect($baseUnit->is(1))->toEqual(1);

    $centimetre = $areaUnits->get('square_centimetre');
    expect($centimetre)->not->toBeNull();
    expect($centimetre->is(1))->toEqual(10 ** -4);
    expect($centimetre->getSymbol())->toEqual('cm²');

    $kilometre = $areaUnits->get('square_kilometre');
    expect($kilometre)->not->toBeNull();
    expect($kilometre->is(1))->toEqual(10 ** 6);
    expect($kilometre->getSymbol())->toEqual('km²');

    /// Imperial Units
    $squareInch = $areaUnits->get('square_inch');
    expect($squareInch)->not->toBeNull();
    expect($squareInch->getSymbol())->toEqual('in²');
    expect($squareInch->is(1))->toEqual(0.00064516);

    $squareFeet = $areaUnits->get('square_feet');
    expect($squareFeet)->not->toBeNull();
    expect($squareFeet->getSymbol())->toEqual('ft²');
    expect($squareFeet->is(1))->toEqual(0.09290304);

    $squareYard = $areaUnits->get('square_yard');
    expect($squareYard)->not->toBeNull();
    expect($squareYard->getSymbol())->toEqual('yd²');
    expect($squareYard->is(1))->toEqual(0.83612736);

    $acre = $areaUnits->get('acre');
    expect($acre)->not->toBeNull();
    expect($acre->getSymbol())->toEqual('ac');
    expect($acre->is(1))->toEqual(4046.8564224);

    $squareMile = $areaUnits->get('square_mile');
    expect($squareMile)->not->toBeNull();
    expect($squareMile->getSymbol())->toEqual('mi²');
    expect($squareMile->is(1))->toEqual(2589988.1103360);
});

test('length units', function () {
    $lengthUnits = new LengthUnits();
    expect($lengthUnits->getName())->toEqual('length');

    $baseUnit = $lengthUnits->getBaseUnit();
    expect($baseUnit->getLabel())->toEqual('metre');
    expect($baseUnit->getSymbol())->toEqual('m');

    $nanometre = $lengthUnits->get('nanometre');
    expect($nanometre)->not->toBeNull();
    expect($nanometre->is(1))->toEqual(10 ** -9);
    expect($nanometre->getSymbol())->toEqual('nm');

    $micrometre = $lengthUnits->get('micrometre');
    expect($micrometre)->not->toBeNull();
    expect($micrometre->is(1))->toEqual(10 ** -6);
    expect($micrometre->getSymbol())->toEqual('µm');

    $millimetre = $lengthUnits->get('millimetre');
    expect($millimetre)->not->toBeNull();
    expect($millimetre->is(1))->toEqual(10 ** -3);

    $centimetre = $lengthUnits->get('centimetre');
    expect($centimetre)->not->toBeNull();
    expect($centimetre->is(1))->toEqual(10 ** -2);
    expect($centimetre->getSymbol())->toEqual('cm');

    $kilometre = $lengthUnits->get('kilometre');
    expect($kilometre)->not->toBeNull();
    expect($kilometre->is(1))->toEqual(1000);
    expect($kilometre->getSymbol())->toEqual('km');

    /// Imperial Units
    $inch = $lengthUnits->get('inch');
    expect($inch)->not->toBeNull();
    expect($inch->getLabel())->toEqual('inch');
    expect($inch->getSymbol())->toEqual('in');
    expect($inch->is(1))->toEqual(0.0254);

    $feet = $lengthUnits->get('feet');
    expect($feet)->not->toBeNull();
    expect($feet->getLabel())->toEqual('feet');
    expect($feet->getSymbol())->toEqual('ft');
    expect($feet->is(1))->toEqual(0.3048);

    $yard = $lengthUnits->get('yard');
    expect($yard)->not->toBeNull();
    expect($yard->getLabel())->toEqual('yard');
    expect($yard->getSymbol())->toEqual('yd');
    expect($yard->is(1))->toEqual(0.9144);

    $mile = $lengthUnits->get('mile');
    expect($mile)->not->toBeNull();
    expect($mile->getLabel())->toEqual('mile');
    expect($mile->getSymbol())->toEqual('mi');
    expect($mile->is(1))->toEqual(1609.344);
});

test('mass units', function () {
    $massUnits = new MassUnits();
    expect($massUnits->getName())->toEqual('mass');

    $baseUnit = $massUnits->getBaseUnit();
    expect($baseUnit)->not->toBeNull();
    expect($baseUnit->getLabel())->toEqual('gram');
    expect($baseUnit->getSymbol())->toEqual('g');

    $milligram = $massUnits->get('milligram');
    expect($milligram)->not->toBeNull();
    expect($milligram->is(1))->toEqual(10 ** -3);
    expect($milligram->getSymbol())->toEqual('mg');

    $kilogram = $massUnits->get('kilogram');
    expect($kilogram)->not->toBeNull();
    expect($kilogram->is(1))->toEqual(1000);
    expect($kilogram->getSymbol())->toEqual('kg');

    $ton = $massUnits->get('ton');
    expect($ton)->not->toBeNull();
    expect($ton->is(1))->toEqual($kilogram->is(1000));
    expect($ton->getSymbol())->toEqual('t');

    /// Imperial Units
    $ounce = $massUnits->get('ounce');
    expect($ounce)->not->toBeNull();
    expect($ounce->getSymbol())->toEqual('oz');
    expect($ounce->is(1))->toEqual(28.349523125);

    $pound = $massUnits->get('pound');
    expect($pound)->not->toBeNull();
    expect($pound->getSymbol())->toEqual('lb');
    expect($pound->is(1))->toEqual(453.59237);
});

test('temperature units', function () {
    $temperatureUnits = new TemperatureUnits();
    expect($temperatureUnits->getName())->toEqual('temperature');

    $baseUnit = $temperatureUnits->getBaseUnit();
    expect($baseUnit)->not->toBeNull();
    expect($baseUnit->getLabel())->toEqual('kelvin');
    expect($baseUnit->getSymbol())->toEqual('K');

    $celsius = $temperatureUnits->get('celsius');
    expect($celsius)->not->toBeNull();
    expect($celsius->getLabel())->toEqual('celsius');
    expect($celsius->getSymbol())->toEqual('°C');
    expect($celsius->is(0))->toEqual(273.15);

    $fahrenheit = $temperatureUnits->get('fahrenheit');
    expect($fahrenheit)->not->toBeNull();
    expect($fahrenheit->getLabel())->toEqual('fahrenheit');
    expect($fahrenheit->getSymbol())->toEqual('°F');
    expect($fahrenheit->is(0))->toEqual(255.3722222222222);

    $réaumur = $temperatureUnits->get('réaumur');
    expect($réaumur)->not->toBeNull();
    expect($réaumur->getLabel())->toEqual('réaumur');
    expect($réaumur->getSymbol())->toEqual('°Ré');
    expect($réaumur->is(0))->toEqual(273.15);
    expect($réaumur->is(80))->toEqual(373.15);
});

test('time units', function () {
    $timeUnits =  new TimeUnits();
    expect($timeUnits->getName())->toEqual('time');

    $baseUnit = $timeUnits->getBaseUnit();
    expect($baseUnit->getLabel())->toEqual('second');
    expect($baseUnit->getSymbol())->toEqual('s');

    $minute = $timeUnits->get('minute');
    expect($minute->getLabel())->toEqual('minute');
    expect($minute->getSymbol())->toEqual('min');
    expect($minute->is(1))->toEqual(60);

    $hour = $timeUnits->get('hour');
    expect($hour->getLabel())->toEqual('hour');
    expect($hour->getSymbol())->toEqual('h');
    expect($hour->is(1))->toEqual($minute->is(60));

    $day = $timeUnits->get('day');
    expect($day->getLabel())->toEqual('day');
    expect($day->getSymbol())->toEqual('d');
    expect($day->is(1))->toEqual($hour->is(24));

    $year = $timeUnits->get('year');
    expect($year->getLabel())->toEqual('year');
    expect($year->getSymbol())->toEqual('a');
    expect($year->is(1))->toEqual($day->is(365));
});

test('volume units', function () {
    $volumeUnits = new VolumeUnits();
    expect($volumeUnits->getName())->toEqual('volume');

    $baseUnit = $volumeUnits->getBaseUnit();
    expect($baseUnit)->not->toBeNull();
    expect($baseUnit->getLabel())->toEqual('cubic_metre');
    expect($baseUnit->getSymbol())->toEqual('m³');

    $millilitre = $volumeUnits->get('millilitre');
    expect($millilitre)->not->toBeNull();
    expect($millilitre->is(1))->toEqual(10 ** -6);
    expect($millilitre->getSymbol())->toEqual('ml');

    $litre = $volumeUnits->get('litre');
    expect($litre)->not->toBeNull();
    expect($litre->is(1))->toEqual(10 ** -3);
    expect($litre->getSymbol())->toEqual('l');

    // Imperial Units
    $fluidOunce = $volumeUnits->get('fluid_ounce_us');
    expect($fluidOunce)->not->toBeNull();
    expect($fluidOunce->getSymbol())->toEqual('fl oz');
    expect($fluidOunce->is(1))->toEqual(2.95735295625e-5);
    expect($fluidOunce->is(1))->toEqualWithDelta($millilitre->is(29.5735295625), self::INACCURACY);

    $pint = $volumeUnits->get('pint_us');
    expect($pint)->not->toBeNull();
    expect($pint->getSymbol())->toEqual('pt');
    expect($pint->is(1))->toEqualWithDelta($millilitre->is(473.176473), self::INACCURACY);

    $gallon = $volumeUnits->get('gallon_us');
    expect($gallon)->not->toBeNull();
    expect($gallon->getSymbol())->toEqual('gal');
    expect($gallon->is(1))->toEqualWithDelta($litre->is(3.785411784), self::INACCURACY);

    // Imperal Conversions
    expect($gallon->is(1 / 8))->toEqualWithDelta($pint->is(1), self::INACCURACY);
    expect($fluidOunce->is(16))->toEqualWithDelta($pint->is(1), self::INACCURACY);

    $cubicMile = $volumeUnits->get('cubic_mile');
    expect($cubicMile)->not->toBeNull();
    expect($cubicMile->getSymbol())->toEqual('mi³');
    expect($cubicMile->is(1))->toEqualWithDelta($baseUnit->is(4168181825.44058), self::INACCURACY);
});

test('speed units', function () {
    $speedUnits = new SpeedUnits();

    $baseUnit = $speedUnits->getBaseUnit();

    expect($baseUnit->getLabel())->toEqual('metre_per_second');
    expect($baseUnit->getSymbol())->toEqual('m/s');
    expect($baseUnit->is(1))->toEqual(1);

    $kmh = $speedUnits->get('kilometre_per_hour');
    expect($kmh->getSymbol())->toEqual('km/h');
    expect($kmh->is(3.6))->toEqualWithDelta(1, self::INACCURACY);

    $timeUnits = new TimeUnits();
    $lengthUnits = new LengthUnits();

    $msToKmhFactor = $lengthUnits->get('kilometre')->is(1) / $timeUnits->get('hour')->is(1);
    expect($msToKmhFactor)->toEqualWithDelta($kmh->is(1), self::INACCURACY);

    $mph = $speedUnits->get('mile_per_hour');
    expect($mph->getSymbol())->toEqual('mph');
    expect($mph->is(1))->toEqualWithDelta(0.44704, self::INACCURACY);

    $msToMphFactor = $lengthUnits->get('mile')->is(1) / $timeUnits->get('hour')->is(1);
    expect($msToMphFactor)->toEqualWithDelta($mph->is(1), self::INACCURACY);

    expect($mph->is(1))->toEqualWithDelta($kmh->is(1.609344), self::INACCURACY);
});

test('force units', function () {
    $forceUnits = new ForceUnits();

    $baseUnit = $forceUnits->getBaseUnit();
    expect($baseUnit->getLabel())->toEqual('newton');
    expect($baseUnit->getSymbol())->toEqual('N');

    $kN = $forceUnits->get('kilonewton');
    expect($kN->getSymbol())->toEqual('kN');
    expect($kN->is(1))->toEqual(1000);
});

test('pressure units', function () {
    $pressureUnits = new PressureUnits();

    // Pascal
    $baseUnit = $pressureUnits->getBaseUnit();
    expect($baseUnit->getLabel())->toEqual('pascal');
    expect($baseUnit->getSymbol())->toEqual('Pa');

    $kN = $pressureUnits->get('kilopascal');
    expect($kN->getSymbol())->toEqual('kPa');
    expect($kN->is(1))->toEqual(1000);

    $hN = $pressureUnits->get('hectopascal');
    expect($hN->getSymbol())->toEqual('hPa');
    expect($hN->is(1))->toEqual(100);

    // Bar
    $bar = $pressureUnits->get('bar');
    expect($bar->getSymbol())->toEqual('bar');
    expect($bar->is(1))->toEqual(100000);

    $decibar = $pressureUnits->get('decibar');
    expect($decibar->getSymbol())->toEqual('dbar');
    expect($decibar->is(1))->toEqual(10000);

    $mbar = $pressureUnits->get('millibar');
    expect($mbar->getSymbol())->toEqual('mbar');
    expect($mbar->is(1))->toEqual(100);

    // Various
    $psi = $pressureUnits->get('pound_per_square_inch');
    expect($psi->getSymbol())->toEqual('psi');
    expect($psi->is(0.0001450377438972831))->toEqualWithDelta(1, self::INACCURACY);

    $torr = $pressureUnits->get('torr');
    expect($torr->getSymbol())->toEqual('Torr');
    expect($torr->is(0.0075006150504341364))->toEqualWithDelta(1, self::INACCURACY);

    $at = $pressureUnits->get('technical_atmosphere');
    expect($at->getSymbol())->toEqual('at');
    expect($at->is(1.019716212977928e-5))->toEqualWithDelta(1, self::INACCURACY);

    $atm = $pressureUnits->get('standard_atmosphere');
    expect($atm->getSymbol())->toEqual('atm');
    expect($atm->is(9.869232667160128e-6))->toEqualWithDelta(1, self::INACCURACY);
});

test('volumetric flow', function () {
    $volumetricFlowUnits = new VolumetricFlowUnits();

    $baseUnit = $volumetricFlowUnits->getBaseUnit();
    expect($baseUnit->getLabel())->toEqual('cubic_metre_per_second');
    expect($baseUnit->getSymbol())->toEqual('m³/s');

    $lps = $volumetricFlowUnits->get('litre_per_second');
    expect($lps->getSymbol())->toEqual('l/s');
    expect($lps->is(1))->toEqual(10 ** -3);
});

test('quotients', function () {
    $quotientUnits = new QuotientUnits();

    $baseUnit = $quotientUnits->getBaseUnit();
    expect($baseUnit->getSymbol())->toEqual('ppm');
    expect($baseUnit->getLabel())->toEqual('parts per million');

    $percent = $quotientUnits->get('percent');
    expect($percent->getSymbol())->toEqual('%');
    expect($percent->is(1e-4))->toEqual(1);
    expect($percent->is(100))->toEqual(1e6);
});

test('import error wrong value', function () {
    $importValue = 10;
    $this->expectException(InvalidDataException::class);
    $this->expectExceptionMessage('The value must be a string.');
    SiUnitAttribute::fromImport($importValue);
});

test('import error wrong format no separator', function () {
    $importValue = "wrong format";
    $this->expectException(InvalidDataException::class);
    $this->expectExceptionMessage('Provided data has the wrong format, expected: value;unit');
    SiUnitAttribute::fromImport($importValue);
});

test('import error wrong format too many separators', function () {
    $importValue = "value;unit;extra";
    $this->expectException(InvalidDataException::class);
    $this->expectExceptionMessage('Provided data has the wrong format, expected: value;unit');
    SiUnitAttribute::fromImport($importValue);
});

test('import error not anumber', function () {
    $importValue = "not a number;unit";
    $this->expectException(InvalidDataException::class);
    $this->expectExceptionMessage('The value must be a number: section 1 => not a number');
    SiUnitAttribute::fromImport($importValue);
});

test('import error not aunit', function () {
    $importValue = "2;not a unit";
    $this->expectException(InvalidDataException::class);
    $this->expectExceptionMessage('Unit does not exist: section 2 => not a unit');
    SiUnitAttribute::fromImport($importValue);
});

test('import success', function () {
    $importValue = "10.5;km";
    $expected = json_encode([
        'value' => 10.5,
        'unit' => 'km',
        'normalized' => 10500,
    ]);
    expect(SiUnitAttribute::fromImport($importValue))->toEqual($expected);
});

test('serialize', function () {
    $data = "{
            'value': 10.5,
            'unit': 'km',
            'normalized': 10500,
        }";
    $expected = json_decode($data);
    expect(SiUnitAttribute::serialize($data))->toEqual($expected);
});

test('unserialize conflict no unit', function () {
    $data = [
        'value' => 10.5,
    ];
    $this->expectException(InvalidDataException::class);
    $this->expectExceptionMessage('Unit does not exist');
    SiUnitAttribute::unserialize($data);
});

test('unserialize conflict invalid unit', function () {
    $data = [
        'value' => 10.5,
        'unit' => "invalid unit",
    ];
    $this->expectException(InvalidDataException::class);
    $this->expectExceptionMessage('Unit does not exist');
    SiUnitAttribute::unserialize($data);
});

test('unserialize success', function () {
    $data = [
        'value' => 10.5,
        'unit' => 'km',
    ];
    $expected = $data;
    $expected["normalized"] = 10500;
    $expected = json_encode($expected);
    expect(SiUnitAttribute::unserialize($data))->toEqual($expected);
});

test('parse export', function () {
    $testValue = AttributeValue::find(77);
    $parseResult = AttributeBase::serializeExportData($testValue);

    expect($parseResult)->toEqual('15.99;kilogram');
});
