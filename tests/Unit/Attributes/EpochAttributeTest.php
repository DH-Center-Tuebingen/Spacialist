<?php

use App\AttributeTypes\EpochAttribute;
use App\AttributeValue;
use App\Exceptions\InvalidDataException;
use PHPUnit\Framework\Attributes\DataProvider;


test('from import truthy', function ($input) {
    $this->expectNotToPerformAssertions(EpochAttribute::class);
    EpochAttribute::fromImport($input);
})->with('truthyProvider');

test('from import return values', function ($input, $expected) {
    expect(EpochAttribute::fromImport($input))->toEqual($expected);
})->with('truthyProvider');

test('from import falsy', function ($input) {
    $this->expectException(InvalidDataException::class);
    EpochAttribute::fromImport($input);
})->with('falsyProvider');

test('parse export', function () {
    $testValue = AttributeValue::find(62);
    $parseResult = EpochAttribute::parseExport(json_encode($testValue->getValue()));

    expect($parseResult)->toEqual('-340;-300;Eisenzeit');
});

test('parse export wihtout epoch', function () {
    $testValue = AttributeValue::find(62);

    // remove epoch key from json data
    $jsonVal = json_decode($testValue->json_val);
    $jsonVal->epoch = null;

    // and store value
    $testValue->json_val = json_encode($jsonVal);

    $parseResult = EpochAttribute::parseExport(json_encode($testValue->getValue()));
    expect($parseResult)->toEqual('-340;-300;');
});

dataset('truthyProvider', function () {
    $baseData = [
        "start" => 100,
        "startLabel" => "bc",
        "end" => 100,
        "endLabel" => "ad",
    ];

    $epochData = $baseData;
    $epochData["epoch"] = ["id"=>59, "concept_url" => "https://spacialist.escience.uni-tuebingen.de/<user-project>/steinzeit#20171220165355"];
    $epochData = json_encode($epochData);

    $noEpochData = $baseData;
    $noEpochData["epoch"] = null;
    $noEpochData = json_encode($noEpochData);

    return [
        "no value" => ["", null],
        "corret value" => ["-100;100;Steinzeit", $epochData],
        "correct value with no epoch" => ["-100;100;", $noEpochData],
        //TODO :: need english epoch, this is currently not in the test data
    ];
});

dataset('falsyProvider', function () {
    return [
        "missing data" => ["-100;100"],
        "epoch does not exist" => ["-100;100;Moderne"],
        "floating point" => ["-100.5;100;Steinzeit"],
    ];
});
