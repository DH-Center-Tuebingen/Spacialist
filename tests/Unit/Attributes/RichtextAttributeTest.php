<?php

use App\AttributeTypes\RichtextAttribute;
use App\Exceptions\InvalidDataException;
use PHPUnit\Framework\Attributes\DataProvider;


test('from import truthy', function ($input) {
    $this->expectNotToPerformAssertions(RichtextAttribute::class);
    RichtextAttribute::fromImport($input);
})->with('truthyProvider');

test('from import return values', function ($input, $expected) {
    expect(RichtextAttribute::fromImport($input))->toEqual($expected);
})->with('truthyProvider');

test('from import falsy', function ($input) {
    $this->expectException(InvalidDataException::class);
    RichtextAttribute::fromImport($input);
})->with('falsyProvider');

dataset('truthyProvider', function () {
    return [
        "empty string" => ["", null],
        "string" => ["test", "test"],
        "markdown string" => ["# test", "# test"],
    ];
});

dataset('falsyProvider', function () {
    return [
        "fails on integer" => [1],
        "fails on float" => [1.1],
        "fails on boolean" => [true],
    ];
});
