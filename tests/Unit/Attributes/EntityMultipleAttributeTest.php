<?php

use App\AttributeTypes\AttributeBase;
use App\AttributeTypes\EntityMultipleAttribute;
use App\AttributeValue;
use App\Exceptions\InvalidDataException;
use PHPUnit\Framework\Attributes\DataProvider;


test('from import truthy', function ($input) {
    $this->expectNotToPerformAssertions(EntityMultipleAttribute::class);
    EntityMultipleAttribute::fromImport($input);
})->with('truthyProvider');

test('from import return values', function ($input, $expected) {
    expect(EntityMultipleAttribute::fromImport($input))->toEqual($expected);
})->with('truthyProvider');

test('from import falsy', function ($input) {
    $this->expectException(InvalidDataException::class);
    EntityMultipleAttribute::fromImport($input);
})->with('falsyProvider');

test('parse export', function () {
    $testValue = AttributeValue::find(75);
    $parseResult = AttributeBase::serializeExportData($testValue);

    expect($parseResult)->toEqual('Inv. 31;Befund 1');
});

dataset('truthyProvider', function () {
    $dataJson = json_encode([3, 4]);

    return [
        "no value" => ["", null],
        "value in english" => ["Inv. 1234;Inv. 124", $dataJson],
        "value with whitespace" => [" Inv. 1234 ; Inv. 124 ", $dataJson],
    ];
});

dataset('falsyProvider', function () {
    return [
        "fail when input is not a string (int)" => [1],
        "fail when input is not a string (bool)" => [true],
        "fail when one input is not a valid entity name" => ["Inv. 1234;invalid entity"],
        "fail when case sensitivity is not considered" => ["Inv. 1234;inv. 124"],
    ];
});
