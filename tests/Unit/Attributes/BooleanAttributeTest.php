<?php

use App\AttributeTypes\BooleanAttribute;
use PHPUnit\Framework\Attributes\DataProvider;


test('from import truthy', function ($input) {
    expect(BooleanAttribute::fromImport($input))->toBeTrue();
})->with('truthyProvider');

test('from import falsy', function ($input) {
    expect(BooleanAttribute::fromImport($input))->toBeFalse();
})->with('falsyProvider');

dataset('truthyProvider', function () {
    return [
        [true],
        [1],
        ['1'],
        ['true'],
        ['t'],
        ['x'],
        ['wahr'],
        ['w'],
    ];
});

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
