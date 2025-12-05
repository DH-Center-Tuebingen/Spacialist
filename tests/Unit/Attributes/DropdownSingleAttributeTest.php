<?php

use App\AttributeTypes\AttributeBase;
use App\AttributeTypes\DropdownSingleAttribute;
use App\AttributeValue;
use App\Exceptions\InvalidDataException;
use PHPUnit\Framework\Attributes\DataProvider;


test('from import truthy', function ($input) {
    $this->expectNotToPerformAssertions(DropdownSingleAttribute::class);
    DropdownSingleAttribute::fromImport($input);
})->with('truthyProvider');

test('from import return values', function ($input, $expected) {
    expect(DropdownSingleAttribute::fromImport($input))->toEqual($expected);
})->with('truthyProvider');

test('from import falsy', function ($input) {
    $this->expectException(InvalidDataException::class);
    DropdownSingleAttribute::fromImport($input);
})->with('falsyProvider');

test('parse export', function () {
    $testValue = AttributeValue::find(60);
    $parseResult = AttributeBase::serializeExportData($testValue);

    expect($parseResult)->toEqual('Graben');
});

dataset('truthyProvider', function () {
    return [
        "no value" => ["", null],
        "value in english" => ["Find","https://spacialist.escience.uni-tuebingen.de/<user-project>/fundobjekt#20171220094921"],
        "value in german" => ["Fundobjekt", "https://spacialist.escience.uni-tuebingen.de/<user-project>/fundobjekt#20171220094921"],
        "value with whitespace" => [" Find ","https://spacialist.escience.uni-tuebingen.de/<user-project>/fundobjekt#20171220094921"],
    ];
});

dataset('falsyProvider', function () {
    return [
        "fail when input is not a string (int)" => [1],
        "fail when input is not a string (bool)" => [true],
        "fail when input is not a valid concept/label in the vocabulary" => ["Fund"],
        "fail when case is not correct" => ["fundobjekt"],
    ];
});
