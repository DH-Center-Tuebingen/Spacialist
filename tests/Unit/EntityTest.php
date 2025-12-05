<?php

use App\Entity;


test('get all children', function () {
    $entity = Entity::find(2);
    $childrenArray = $entity->getAllChildren();
    expect(count($childrenArray))->toEqual(4);
    $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys([
        [
            '_name' => 'Befund 1',
            '_parent' => 'Site A',
            '_entity_type' => 'Feature',
            '_entity_type_id' => 4,
        ],
        [
            '_name' => 'Inv. 1234',
            '_parent' => 'Site A\\\\Befund 1',
            '_entity_type' => 'Pottery',
            '_entity_type_id' => 5,
        ],
        [
            '_name' => 'Inv. 124',
            '_parent' => 'Site A\\\\Befund 1',
            '_entity_type' => 'Pottery',
            '_entity_type_id' => 5,
        ],
        [
            '_name' => 'Inv. 31',
            '_parent' => 'Site A\\\\Befund 1',
            '_entity_type' => 'Stone',
            '_entity_type_id' => 6,
            12 => 3.5,
        ],
    ], $childrenArray,
    [
        '_name',
        '_parent',
        '_entity_type',
        '_entity_type_id',
        12,
    ]);
});

test('relations', function () {
    $entity = Entity::with(['child_entities', 'entity_type', 'root_entity', 'bibliographies', 'attributes'])->find(3);

    expect($entity->child_entities->count())->toEqual(0);
    expect($entity->entity_type->id)->toEqual(5);
    expect($entity->entity_type->id)->toEqual($entity->entity_type_id);
    expect($entity->root_entity->id)->toEqual(2);
    expect($entity->bibliographies->count())->toEqual(0);
    expect($entity->attributes->count())->toEqual(7);
    expect(count($entity->parentIds))->toEqual(3);
    expect(count($entity->parentNames))->toEqual(3);
    $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys([
        [
            'id' => 2,
            'pivot' => [
                'int_val' => 35,
            ],
        ],
        [
            'id' => 3,
            'pivot' => [
                'json_val' => '[{"id": 18, "concept_url": "https://spacialist.escience.uni-tuebingen.de/<user-project>/rot#20171220100515"}, {"id": 20, "concept_url": "https://spacialist.escience.uni-tuebingen.de/<user-project>/grun#20171220100524"}]',
            ],
        ],
        [
            'id' => 4,
            'pivot' => [
                'int_val' => 1,
            ],
        ],
        [
            'id' => 5,
            'pivot' => [
                'json_val' => '[{"6": {"id": 35, "concept_url": "https://spacialist.escience.uni-tuebingen.de/<user-project>/rand#20171220105447"}, "7": {"id": 40, "concept_url": "https://spacialist.escience.uni-tuebingen.de/<user-project>/riefung#20171220105513"}}, {"6": {"id": 35, "concept_url": "https://spacialist.escience.uni-tuebingen.de/<user-project>/rand#20171220105447"}, "7": {"id": 41, "concept_url": "https://spacialist.escience.uni-tuebingen.de/<user-project>/kammeindruck#20171220105520"}}]',
            ],
        ],
        [
            'id' => 9,
            'pivot' => [
                'json_val' => '{"B": 4, "H": 5, "unit": "cm"}',
            ],
        ],
        [
            'id' => 11,
            'pivot' => [
                'int_val' => 7,
            ],
        ],
        [
            'id' => 12,
            'pivot' => [
                'dbl_val' => '12.5',
            ],
        ],
    ], $entity->attributes->toArray(),
    ['id', 'pivot']);
    expect($entity->parentIds)->toEqual([
        3, 2, 1
    ]);
    expect($entity->parentNames)->toEqual([
        'Inv. 1234', 'Befund 1', 'Site A'
    ]);
});
