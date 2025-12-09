<?php

use App\AttributeTypes\DoubleAttribute;
use App\Exceptions\InvalidDataException;
use PHPUnit\Framework\Attributes\DataProvider;


test('from import truthy', function ($input) {
    $this->expectNotToPerformAssertions(DoubleAttribute::class);
    DoubleAttribute::fromImport($input);
})->with('truthyProvider');

test('from import return values', function ($input, $expected) {
    expect(DoubleAttribute::fromImport($input))->toEqual($expected);
})->with('truthyProvider');

test('from import falsy', function ($input) {
    $this->expectException(InvalidDataException::class);
    DoubleAttribute::fromImport($input);
})->with('falsyProvider');

dataset('truthyProvider', function () {
    return [
        ["", null],
        [0, "0"],
        [123, "123"],
        [-42, "-42"],
        [8.23, "8.23"],
        [13e3, "13000"],
        [2.3E-3,"0.0023"]
    ];
});

dataset('falsyProvider', function () {
    return [
         // insert failing tests
        ["a"],
        [true],
        [false],
        ["123,23"] // Don't allow comma as decimal separator
    ];
});
