<?php

use App\AttributeTypes\IconclassAttribute;
use App\Exceptions\InvalidDataException;
use PHPUnit\Framework\Attributes\DataProvider;


test('from import truthy', function ($input) {
    $this->expectNotToPerformAssertions(IconclassAttribute::class);
    IconclassAttribute::fromImport($input);
})->with('truthyProvider');

test('from import return values', function ($input, $expected) {
    expect(IconclassAttribute::fromImport($input))->toEqual($expected);
})->with('truthyProvider');

test('from import falsy', function ($input) {
    $this->expectException(InvalidDataException::class);
    IconclassAttribute::fromImport($input);
})->with('falsyProvider');

dataset('truthyProvider', function () {
    return [
        "empty string" => ["", null],
        "string" => ["string", "string"],
    ];
});

dataset('falsyProvider', function () {
    return [
        "number" => [4],
    ];
});
