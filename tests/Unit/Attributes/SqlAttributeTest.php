<?php

use App\AttributeTypes\SqlAttribute;
use App\Exceptions\InvalidDataException;
use PHPUnit\Framework\Attributes\DataProvider;


test('from import falsy', function ($input) {
    $this->expectException(InvalidDataException::class);
    SqlAttribute::fromImport($input);
})->with('falsyProvider');

dataset('falsyProvider', function () {
    return [
        [false],
        [0],
        [-1],
        ['ok'],
        ['0'],
        ['kauderwelsch'],
    ];
});
