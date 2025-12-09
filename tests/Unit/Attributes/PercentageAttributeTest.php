<?php

use App\AttributeTypes\PercentageAttribute;
use App\Exceptions\InvalidDataException;
use PHPUnit\Framework\Attributes\DataProvider;


test('from import truthy', function ($input) {
    $this->expectNotToPerformAssertions(PercentageAttribute::class);
    PercentageAttribute::fromImport($input);
})->with('truthyProvider');

test('from import return values', function ($input, $expected) {
    expect(PercentageAttribute::fromImport($input))->toEqual($expected);
})->with('truthyProvider');

test('from import falsy', function ($input) {
    $this->expectException(InvalidDataException::class);
    PercentageAttribute::fromImport($input);
})->with('falsyProvider');

dataset('truthyProvider', function () {
    return [
            "empty" => ["", null],
            "zero" => ["0", 0],
            "fifty" => ["50", 50],
            "hundred" => ["100", 100],
            "integer zero" => [0, 0],
            "integer fifty" => [50, 50],
            "integer hundred" => [100, 100],
    ];
});

dataset('falsyProvider', function () {
    return [
            "boolean" => [true],
            "float" => [1.1],
            "negative integer" => [-1],
            "negative float" => [-1.1],
            "integer over hundred" => [101],
            "string is float" => ["1.1"],
            "string is negative integer" => ["-1"],
            "string is negative float" => ["-1.1"],
            "string is over hundred" => ["101"],
    ];
});
