<?php

use App\AttributeTypes\AttributeBase;
use App\AttributeTypes\DropdownMultipleAttribute;
use App\AttributeValue;
use App\Exceptions\InvalidDataException;
use PHPUnit\Framework\Attributes\DataProvider;


test('from import truthy', function ($input) {
    $this->expectNotToPerformAssertions(DropdownMultipleAttribute::class);
    DropdownMultipleAttribute::fromImport($input);
})->with('truthyProvider');

test('from import check return values', function ($input, $expected) {
    expect(DropdownMultipleAttribute::fromImport($input))->toEqual($expected);
})->with('truthyProvider');

test('from import falsy', function ($input) {
    $this->expectException(InvalidDataException::class);
    DropdownMultipleAttribute::fromImport($input);
})->with('falsyProvider');

test('parse export', function () {
    $testValue = AttributeValue::find(78);
    $parseResult = AttributeBase::serializeExportData($testValue);

    expect($parseResult)->toEqual('rot;grün');
});

dataset('truthyProvider', function () {
    $find = [
        "id" => 3,
        "concept_url" => "https://spacialist.escience.uni-tuebingen.de/<user-project>/fundobjekt#20171220094921",
    ];

    $pottery = [
        "id" => 9,
        "concept_url" => "https://spacialist.escience.uni-tuebingen.de/<user-project>/keramik#20171220095651",
    ];

    $dataJson = json_encode([$find, $pottery]);

    return [
        "no value" => ["", null],
        "value in english" => ["Find;Pottery", $dataJson],
        "value in german" => ["Fundobjekt;Keramik", $dataJson],
        "value with whitespace" => [" Find ; Pottery ", $dataJson],
        "value with different languages" => ["Find;Keramik", $dataJson]
    ];
});

dataset('falsyProvider', function () {
    return [
        "fail when input is not a string (int)" => [1],
        "fail when input is not a string (bool)" => [true],
        "fail when one input is not a valid concept/label in the vocabulary" => ["Fund;Pottery"],
        "fail when case sensitivity is not considered" => ["find;pottery"],
    ];
});
