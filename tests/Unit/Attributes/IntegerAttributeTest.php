<?php

use App\AttributeTypes\IntegerAttribute;
use App\Exceptions\InvalidDataException;
use PHPUnit\Framework\Attributes\DataProvider;


test('from import truthy', function ($input) {
    $this->expectNotToPerformAssertions(IntegerAttribute::class);
    IntegerAttribute::fromImport($input);
})->with('truthyProvider');

test('from import return values', function ($input, $expected) {
    expect(IntegerAttribute::fromImport($input))->toEqual($expected);
})->with('truthyProvider');

test('from import falsy', function ($input) {
    $this->expectException(InvalidDataException::class);
    IntegerAttribute::fromImport($input);
})->with('falsyProvider');

dataset('truthyProvider', function () {
    return [
            "empty string" => ["", null],
            "zero" => [0, 0],
            "positive integer" => [1, 1],
            "negative integer" => [-1, -1],
            "string integer" => ["1", 1],
            "big integer (max int on 32 bit system)" => ["2147483647", 2147483647],
            "big negative integer (max int on 32 bit system)" => ["-2147483647", -2147483647],
    ];
});

dataset('falsyProvider', function () {
    return [
            "fail when integer is too big (max int on 64 bit system +1)" => ["9223372036854775808"],
            "fail when float" => [1.1],
            "fail when string" => ["string"],
            "fail when boolean" => [true],
    ];
});
