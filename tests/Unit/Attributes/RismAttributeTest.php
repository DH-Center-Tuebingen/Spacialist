<?php

use App\AttributeTypes\RismAttribute;
use App\Exceptions\InvalidDataException;
use PHPUnit\Framework\Attributes\DataProvider;


test('from import truthy', function ($input) {
    $this->expectNotToPerformAssertions(RismAttribute::class);
    RismAttribute::fromImport($input);
})->with('truthyProvider');

test('from import return values', function ($input, $expected) {
    expect(RismAttribute::fromImport($input))->toEqual($expected);
})->with('truthyProvider');

test('from import falsy', function ($input) {
    $this->expectException(InvalidDataException::class);
    RismAttribute::fromImport($input);
})->with('falsyProvider');

dataset('truthyProvider', function () {
    return [
        "empty string" => ["", null],
        "number as string" => ["600146721", "600146721"],
        "number as string with spaces" => [" 600146721 ", "600146721"],
        "number can start with 0" => ["00000600146721", "00000600146721"],
    ];
});

dataset('falsyProvider', function () {
    return [
         // insert failing tests
        "fails on integer value" => [1],
        "fails on float" => [1.1],
        "fails on float string" => ["1.1"],
        "fails on boolean" => [true],
        "fails on non-numeric string" => ["string"],
        "fails on negative integer" => [-1],
        "fails on negative integer string" => ["-1"],
    ];
});
