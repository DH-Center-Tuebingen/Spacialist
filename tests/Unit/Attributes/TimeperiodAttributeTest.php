<?php

use App\AttributeTypes\AttributeBase;
use App\AttributeTypes\TimeperiodAttribute;
use App\AttributeValue;
use App\DataTypes\TimePeriod;
use App\Exceptions\InvalidDataException;
use PHPUnit\Framework\Attributes\DataProvider;


test('from import truthy', function ($input) {
    $this->expectNotToPerformAssertions(TimeperiodAttribute::class);
    TimeperiodAttribute::fromImport($input);
})->with('truthyProvider');

test('from import return values', function ($input, $expected) {
    // Convert the TimePeriod object to a json string
    if($expected != null){
        $expected = json_encode($expected);
    }
    expect(TimeperiodAttribute::fromImport($input))->toEqual($expected);
})->with('truthyProvider');

test('from import falsy', function ($input) {
    $this->expectException(InvalidDataException::class);
    TimeperiodAttribute::fromImport($input);
})->with('falsyProvider');

test('parse export', function () {
    $testValue = AttributeValue::find(74);
    $parseResult = TimeperiodAttribute::parseExport(json_encode($testValue->getValue()));

    expect($parseResult)->toEqual('-200;-100');
});

test('parse export on attribute base', function () {
    $testValue = AttributeValue::find(74);
    $parseResult = AttributeBase::serializeExportData($testValue);

    expect($parseResult)->toEqual('-200;-100');
});

dataset('truthyProvider', function () {
    return [
        "empty string" => ["", null],
        "string" => ["-100;200", new TimePeriod(-100, 200)],
        "string with spaces" => [" -100 ; 200 ", new TimePeriod(-100, 200)],
    ];
});

dataset('falsyProvider', function () {
    return [
        "fails on integer" => [1],
        "fails on float" => [1.1],
        "fails on boolean" => [true],
        "fails on string without semicolon" => ["-100"],
        "fails on string with more than one semicolon" => ["-100;200;300"],
        "fails on string with negative values" => ["-100;-200"],
        "fails on string with non-numeric values (1)" => ["string;200"],
        "fails on string with non-numeric values (2)" => ["-100;string"],
        "failsl if one value is a float (1)" => ["-100.5;200"],
        "failsl if one value is a float (2)" => ["-100;200.1"],
    ];
});
