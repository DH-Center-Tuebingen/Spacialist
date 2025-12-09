<?php

use App\AttributeTypes\StringAttribute;
use App\Exceptions\InvalidDataException;
use PHPUnit\Framework\Attributes\DataProvider;


test('from import truthy', function ($input) {
    $this->expectNotToPerformAssertions(StringAttribute::class);
    StringAttribute::fromImport($input);
})->with('truthyProvider');

test('from import return values', function ($input, $expected) {
    expect(StringAttribute::fromImport($input))->toEqual($expected);
})->with('truthyProvider');

test('from import falsy', function ($input) {
    $this->expectException(InvalidDataException::class);
    StringAttribute::fromImport($input);
})->with('falsyProvider');

dataset('truthyProvider', function () {
    return [
        "empty string" => ["", null],
        "string" => ["test", "test"],
    ];
});

dataset('falsyProvider', function () {
    return [
        "fails on integer" => [1],
        "fails on float" => [1.1],
        "fails on boolean" => [true],
    ];
});
