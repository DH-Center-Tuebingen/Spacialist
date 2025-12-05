<?php

use App\EntityType;


test('relations', function () {
    $type = EntityType::with(['entities', 'attributes', 'sub_entity_types', 'thesaurus_concept'])->find(4);

    expect($type->entities->count())->toEqual(1);
    expect($type->entities[0]->id)->toEqual(2);
    expect($type->attributes->count())->toEqual(6);
    expect($type->attributes[0]->id)->toEqual(14);
    expect($type->attributes[0]->pivot->position)->toEqual(1);
    expect($type->attributes[1]->id)->toEqual(16);
    expect($type->attributes[1]->pivot->position)->toEqual(2);
    expect($type->attributes[2]->id)->toEqual(17);
    expect($type->attributes[2]->pivot->position)->toEqual(3);
    expect($type->attributes[3]->id)->toEqual(10);
    expect($type->attributes[3]->pivot->position)->toEqual(4);
    expect($type->attributes[4]->id)->toEqual(13);
    expect($type->attributes[4]->pivot->position)->toEqual(5);
    expect($type->sub_entity_types->count())->toEqual(0);
    expect($type->thesaurus_concept->id)->toEqual(2);
});
