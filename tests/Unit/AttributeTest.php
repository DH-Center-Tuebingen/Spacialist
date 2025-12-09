<?php

use App\Attribute;


test('relations', function () {
    $attribute = Attribute::with(['children', 'entities', 'entity_types', 'parent', 'thesaurus_root_concept', 'thesaurus_concept'])->find(6);

    expect($attribute->children->isEmpty())->toBeTrue();
    expect($attribute->entities->isEmpty())->toBeTrue();
    expect($attribute->entity_types->isEmpty())->toBeTrue();
    expect($attribute->parent->id)->toEqual(5);
    expect($attribute->thesaurus_root_concept->id)->toEqual(33);
    expect($attribute->thesaurus_concept->id)->toEqual(33);

    $attribute = Attribute::with(['children', 'entities', 'entity_types', 'parent', 'thesaurus_root_concept', 'thesaurus_concept'])->find(5);

    expect($attribute->children->count())->toEqual(3);
    expect($attribute->entities->count())->toEqual(3);
    expect($attribute->entity_types->count())->toEqual(1);
    expect($attribute->parent)->toBeNull();
    expect($attribute->thesaurus_root_concept)->toBeNull();
    expect($attribute->thesaurus_concept->id)->toEqual(32);

    $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys([
        ['id' => 6],
        ['id' => 7],
        ['id' => 8],
    ], $attribute->children->toArray(),
    ['id']
    );
    $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys([
        [
            'id' => 4,
            'pivot' => [
                'json_val' => '[{"6": {"id": 37, "concept_url": "https://spacialist.escience.uni-tuebingen.de/<user-project>/schulter#20171220105500"}, "7": {"id": 40, "concept_url": "https://spacialist.escience.uni-tuebingen.de/<user-project>/riefung#20171220105513"}}]',
            ],
        ],
        [
            'id' => 3,
            'pivot' => [
                'json_val' => '[{"6": {"id": 35, "concept_url": "https://spacialist.escience.uni-tuebingen.de/<user-project>/rand#20171220105447"}, "7": {"id": 40, "concept_url": "https://spacialist.escience.uni-tuebingen.de/<user-project>/riefung#20171220105513"}}, {"6": {"id": 35, "concept_url": "https://spacialist.escience.uni-tuebingen.de/<user-project>/rand#20171220105447"}, "7": {"id": 41, "concept_url": "https://spacialist.escience.uni-tuebingen.de/<user-project>/kammeindruck#20171220105520"}}]',
            ],
        ],
        [
            'id' => 8,
            'pivot' => [
                'json_val' => '[{"6": {"id": 39, "concept_url": "https://spacialist.escience.uni-tuebingen.de/<user-project>/boden#20171220105508"}, "7": {"id": 41, "concept_url": "https://spacialist.escience.uni-tuebingen.de/<user-project>/kammeindruck#20171220105520"}}]',
            ],
        ],
    ], $attribute->entities->toArray(),
    ['id', 'pivot']);
    $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys([
        ['id' => 5],
    ], $attribute->entity_types->toArray(),
    ['id']);
});
