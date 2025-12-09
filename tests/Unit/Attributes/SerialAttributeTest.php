<?php

use App\AttributeTypes\SerialAttribute;
use App\Exceptions\InvalidDataException;
use PHPUnit\Framework\Attributes\DataProvider;


test('from import falsy', function ($input) {
    $this->expectException(InvalidDataException::class);
    SerialAttribute::fromImport($input);
})->with('falsyProvider');

dataset('falsyProvider', function () {
    return [
        "fails on import" => [false],
        "fails on 0" => [0],
        "fails on integer" => [1],
        "fails on negative integer" => [-1],
        "fails on string" => ['ok'],
        "fails on string 0" => ['0'],
        "fails on another string" => ['kauderwelsch'],
        "fails on float" => [1.1],
        "fails on float string" => ["1.1"],
    ];
});
