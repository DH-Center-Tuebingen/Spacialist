<?php

use App\AttributeTypes\AttributeBase;
use App\AttributeTypes\EntityAttribute;
use App\AttributeValue;
use App\Exceptions\InvalidDataException;
use PHPUnit\Framework\Attributes\DataProvider;


test('from import truthy', function ($input) {
    $this->expectNotToPerformAssertions(EntityAttribute::class);
    EntityAttribute::fromImport($input);
})->with('truthyProvider');

test('from import return values', function ($input, $expected) {
    expect(EntityAttribute::fromImport($input))->toEqual($expected);
})->with('truthyProvider');

test('from import falsy', function ($input) {
    $this->expectException(InvalidDataException::class);
    EntityAttribute::fromImport($input);
})->with('falsyProvider');

test('parse export', function () {
    $testValue = AttributeValue::find(35);
    $parseResult = AttributeBase::serializeExportData($testValue);

    expect($parseResult)->toEqual('Aufschluss');
});

dataset('truthyProvider', function () {
    return [
        "no value" => ["", null],
        "value" => ["Inv. 1234", 3],
        "value with whitespace" => [" Inv. 1234 ", 3],

    ];
});

dataset('falsyProvider', function () {
    return [
        "fail when input is not a string (int)" => [1],
        "fail when input is not a string (bool)" => [true],
        "fail when input is not a valid concept/label in the vocabulary" => ["Fund"],
        "fail when case is not correct" => ["inv. 1234"],
    ];
});
