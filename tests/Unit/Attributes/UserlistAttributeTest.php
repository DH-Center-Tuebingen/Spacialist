<?php

use App\AttributeTypes\AttributeBase;
use App\AttributeTypes\UserlistAttribute;
use App\AttributeValue;
use App\Exceptions\InvalidDataException;
use PHPUnit\Framework\Attributes\DataProvider;


test('from import truthy', function ($input) {
    $this->expectNotToPerformAssertions(UserlistAttribute::class);
    UserlistAttribute::fromImport($input);
})->with('truthyProvider');

test('from import return values', function ($input, $expected) {
    expect(UserlistAttribute::fromImport($input))->toEqual($expected);
})->with('truthyProvider');

test('from import falsy', function ($input) {
    $this->expectException(InvalidDataException::class);
    UserlistAttribute::fromImport($input);
})->with('falsyProvider');

test('parse export', function () {
    $testValue = AttributeValue::find(76);
    $parseResult = AttributeBase::serializeExportData($testValue);

    expect($parseResult)->toEqual('admin');
});

dataset('truthyProvider', function () {
    return [
        "empty string" => ["", null],
        "single user" => ["admin", "[1]"],
        "multiple users" => ["admin;johndoe", "[1,2]"],
        "multiple users with spaces" => [" admin ; johndoe ", "[1,2]"],
        "single deleted user" => ["garyguest", "[3]"],
        "multiple users with single deleted user" => ["admin;garyguest", "[1,3]"],
    ];
});

dataset('falsyProvider', function () {
    return [
        "fails on integer" => [1],
        "fails on float" => [1.1],
        "fails on boolean" => [true],
        "fails on incorrect user" => ["unknown"],
        "fails on any incorrect user" => ["unknown,unknown2"],
    ];
});
