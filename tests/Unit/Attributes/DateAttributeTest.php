<?php

use App\AttributeTypes\DateAttribute;
use App\Exceptions\InvalidDataException;
use PHPUnit\Framework\Attributes\DataProvider;


test('from import truthy', function ($input) {
    $this->expectNotToPerformAssertions(InvalidDataException::class);
    DateAttribute::fromImport($input);
})->with('truthyProvider');

test('from import falsy', function ($input) {
    $this->expectException(InvalidDataException::class);
    DateAttribute::fromImport($input);
})->with('falsyProvider');

dataset('truthyProvider', function () {
    return [
        "correct format"=>["2024-10-30"],
        "whitespace should be trimmed" => [" 2024-10-30 "],
    ];
});

dataset('falsyProvider', function () {
    return [
        "fail when datetime is passed" =>["2024-10-30 10:00:00"],
        "fail when year has only two letters" => ["24-10-10"],
        "fail for unix timestring" => ["1727876634"],
        "fail for boolean true" => [true],
        "fail for boolean false" => [false],
        "fail for numeric 0" => [0],
        "fail for positive numeric unix timestring" => [1727876634],
        "fail for negative numeric unix timestring" =>[-1727876634],
        "fail for positive numeric float value" => [1727876634.2312],
        "fail for negative numeric float value" => [-1727876634.2312],
    ];
});
