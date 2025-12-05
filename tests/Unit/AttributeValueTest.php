<?php

use App\AttributeValue;


test('relations', function () {
    $value = AttributeValue::with(['entity', 'attribute', 'entity_value', 'concept'])->find(60);

    expect($value->entity->id)->toEqual(2);
    expect($value->attribute->id)->toEqual(14);
    expect($value->concept->id)->toEqual(53);
    expect($value->entity_value)->toBeNull();

    $value = AttributeValue::with(['entity', 'attribute', 'entity_value', 'concept'])->find(35);

    expect($value->entity->id)->toEqual(5);
    expect($value->attribute->id)->toEqual(18);
    expect($value->concept)->toBeNull();
    expect($value->entity_value->id)->toEqual(6);
    expect($value->entity_value->name)->toEqual('Aufschluss');
});

test('get attribute value by id', function () {
    $value = AttributeValue::getValueById(16, 2);
    expect($value)->toEqual('SRID=4326;POINT(8.92 48.45)');

    $value = AttributeValue::getValueById(99, 2);
    expect($value)->toBeNull();
});

test('get attribute value lasteditor', function () {
    $value = AttributeValue::first();
    expect($value->user->id)->toEqual(1);
});
