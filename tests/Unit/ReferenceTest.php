<?php

use App\Reference;


test('relations', function () {
    $ref = Reference::with(['entity', 'attribute', 'bibliography'])->find(3);

    expect($ref->entity->id)->toEqual(1);
    expect($ref->attribute->id)->toEqual(13);
    expect($ref->bibliography->id)->toEqual(1323);
});

test('get reference entry lasteditor', function () {
    $ref = Reference::first();
    expect($ref->user->id)->toEqual(1);
});
