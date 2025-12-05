<?php

use App\AttributeTypes\AttributeBase;
use App\AttributeTypes\DaterangeAttribute;
use App\AttributeValue;
use App\Exceptions\InvalidDataException;
use PHPUnit\Framework\Attributes\DataProvider;


test('from import truthy', function ($input) {
    $this->expectNotToPerformAssertions(DaterangeAttribute::class);
    DaterangeAttribute::fromImport($input);
})->with('truthyProvider');

test('from import falsy', function ($input) {
    $this->expectException(InvalidDataException::class);
    DaterangeAttribute::fromImport($input);
})->with('falsyProvider');

test('parse export', function () {
    $testValue = AttributeValue::find(79);
    $parseResult = AttributeBase::serializeExportData($testValue);

    expect($parseResult)->toEqual('2025-02-01;2025-02-07');
});

dataset('truthyProvider', function () {
    return [
        "correct format" => ["2024-10-25;2024-10-30"],
        "correct if same date" => ["2024-10-25;2024-10-30"],
        "should trim formats" => [" 2024-10-25 ; 2024-10-30 "]
    ];
});

dataset('falsyProvider', function () {
    return [
        "end cannot be before start" => ["2024-10-30;2024-10-25"],
        "fails for datetime" => ["2024-10-30 10:00:00;2024-10-30 10:00:00"],
        "fails for year with two letters" => ["24-10-10;24-10-10"],
        "fail for unix timestring" => ["1727876634;1727876634"],
        "fail for boolean true" => [true],
        "fail for boolean false" => [false],
        "fail for numeric 0" => [0],
        "fail for positive numeric unix timestring" => [1727876634],
        "fail for negative numeric unix timestring" =>[-1727876634],
        "fail for positive numeric float value" => [1727876634.2312],
        "fail for negative numeric float value" => [-1727876634.2312],
    ];
});
