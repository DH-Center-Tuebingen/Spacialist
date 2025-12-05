<?php

use App\AttributeTypes\AttributeBase;
use App\AttributeTypes\ListAttribute;
use App\AttributeValue;
use App\Exceptions\InvalidDataException;
use PHPUnit\Framework\Attributes\DataProvider;


test('from import truthy', function ($input) {
    $this->expectNotToPerformAssertions(ListAttribute::class);
    ListAttribute::fromImport($input);
})->with('truthyProvider');

test('from import return values', function ($input, $expected) {
    if($expected != null)
    $expected = json_encode($expected);

    expect(ListAttribute::fromImport($input))->toEqual($expected);
})->with('truthyProvider');

test('from import falsy', function ($input) {
    $this->expectException(InvalidDataException::class);
    ListAttribute::fromImport($input);
})->with('falsyProvider');

test('parse export', function () {
    $testValue = AttributeValue::find(69);
    $parseResult = AttributeBase::serializeExportData($testValue);

    expect($parseResult)->toEqual('Fundstelle A');
});

dataset('truthyProvider', function () {
    return [
        "empty value" => ["", null],
        "single item" => ["item", ["item"]],
        "multiple items" => ["item1;item2", ["item1", "item2"]],
        "multiple items with spaces" => [" item1 ; item2 ", ["item1", "item2"]],
        "list with empty values" => ["item1;;item2", ["item1", "", "item2"]],
    ];
});

dataset('falsyProvider', function () {
    return [
        "boolean" => [true],
        "integer" => [1],
        "float" => [1.1],
    ];
});
