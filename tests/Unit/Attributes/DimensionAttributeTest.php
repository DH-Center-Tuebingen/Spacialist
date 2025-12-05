<?php

use App\AttributeTypes\AttributeBase;
use App\AttributeTypes\DimensionAttribute;
use App\AttributeValue;
use App\Exceptions\InvalidDataException;
use PHPUnit\Framework\Attributes\DataProvider;


test('from import no exception', function ($input) {
    $this->expectNotToPerformAssertions(DimensionAttribute::class);
    DimensionAttribute::fromImport($input);
})->with('truthyProvider');

test('from import return values', function ($input, $expected) {
    expect(DimensionAttribute::fromImport($input))->toEqual($expected);
})->with('truthyProvider');

test('from import exception', function ($input) {
    $this->expectException(InvalidDataException::class);
    DimensionAttribute::fromImport($input);
})->with('falsyProvider');

test('parse export', function () {
    $testValue = AttributeValue::find(33);
    $parseResult = AttributeBase::serializeExportData($testValue);

    expect($parseResult)->toEqual('10;3;1.5;cm');
});

dataset('truthyProvider', function () {
    return [
        "no value" => ["", null],
        "input using 0" => ["0;0;0;cm", json_encode(["B" => 0, "H" => 0, "T" => 0, "unit" => "cm"])],
        "input using integers" => ["22;51;210;m", json_encode(["B" => 22, "H" => 51, "T" => 210, "unit" => "m"])],
        "input using floats" => ["22.5;51.2;210.3;cm", json_encode(["B" => 22.5, "H" => 51.2, "T" => 210.3, "unit" => "cm"])],
        "input using negative values" => ["-22.5;-51.2;-210.3;cm", json_encode(["B" => -22.5, "H" => -51.2, "T" => -210.3, "unit" => "cm"])], // Discuss: Maybe this should not be allowed?
        "input whitespaces should be trimmed" => [" 1 ; 2 ; 3 ; cm ", json_encode(["B" => 1, "H" => 2, "T" => 3, "unit" => "cm"])],
    ];
});

dataset('falsyProvider', function () {
    return [
        "fail when input is not a string (int)" => [1],
        "fail when input is not a string (bool)" => [true],
        "fail when a dimension is missing" => ["1;2;cm"],
        "fail when no unit is provided" => ["1;2;3"],
        "fail when first is not a number" => ["a;2;3;cm"],
        "fail when second is not a number" => ["1;a;3;cm"],
        "fail when third is not a number" => ["1;2;a;cm"],
    ];
});
