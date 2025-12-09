<?php

use App\Attribute;
use App\AvailableLayer;
use App\AttributeValue;
use App\Entity;
use App\EntityAttribute;
use App\EntityType;
use App\ThConcept;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\ResponseTester;
use Tests\Permission;
use PHPUnit\Framework\Attributes\DataProvider;


test('entity occur count endpoint', function () {
    $response = $this->userRequest()
        ->get('/api/v1/editor/dm/entity_type/occurrence_count/5');

    $this->assertStatus($response, 200);
    $response->assertSimilarJson([3]);
});

test('attribute occur count endpoint', function () {
    $response = $this->userRequest()
        ->get('/api/v1/editor/dm/attribute/occurrence_count/9');

    $this->assertStatus($response, 200);
    $response->assertSimilarJson([4]);
});

test('attribute of entity type occur count endpoint', function () {
    $response = $this->userRequest()
        ->get('/api/v1/editor/dm/attribute/occurrence_count/9/5');

    $this->assertStatus($response, 200);
    $response->assertSimilarJson([3]);
});

test('top entity type count endpoint', function () {
    $response = $this->userRequest()
        ->get('/api/v1/editor/dm/entity_type/top');

    $this->assertStatus($response, 200);
    $response->assertJsonCount(2);
    $response->assertJsonStructure([
        [
            'id',
            'thesaurus_url',
            'is_root',
            'created_at',
            'updated_at'
        ]
    ]);
    $response->assertSimilarJson([
        [
            'id' => 3,
            'thesaurus_url' => 'https://spacialist.escience.uni-tuebingen.de/<user-project>/fundstelle#20171220094911',
            'is_root' => true,
            'created_at' => '2017-12-20T10:03:06.000000Z',
            'updated_at' => '2017-12-20T10:03:06.000000Z',
            'color' => '#FF0000',
        ],
        [
            'id' => 7,
            'thesaurus_url' => 'https://spacialist.escience.uni-tuebingen.de/<user-project>/lagerstatte#20171220165727',
            'is_root' => true,
            'created_at' => '2017-12-20T16:57:41.000000Z',
            'updated_at' => '2017-12-20T16:57:41.000000Z',
            'color' => '#0000FF',
        ]
    ]);
});

test('get attribute endpoint', function () {
    $response = $this->userRequest()
        ->get('/api/v1/editor/dm/attribute');

    $this->assertStatus($response, 200);
    $response->assertJsonCount(21, 'attributes');
    $response->assertJsonStructure([
        'attributes' => [
            '*' => [
                'id',
                'thesaurus_url',
                'datatype',
                'text',
                'thesaurus_root_url',
                'parent_id',
                'created_at',
                'updated_at',
                'recursive',
                'root_attribute_id',
                'is_system',
                'multiple',
                'restrictions',
                'metadata',
                'entity_types_count',
            ]
        ]
    ]);
    $response->assertJson(fn(AssertableJson $json) =>
        $json
            ->has('attributes')
            ->has('attributes.1', fn($json) =>
                $json
                    ->where('id', 2)
                    ->where('thesaurus_url', 'https://spacialist.escience.uni-tuebingen.de/<user-project>/erhaltung#20171220100437')
                    ->where( 'datatype', 'percentage')
                    ->where('thesaurus_root_url', NULL)
                    ->where('created_at', '2017-12-20T10:07:45.000000Z')
                    ->where('updated_at', '2017-12-20T10:07:45.000000Z')
                    ->where('parent_id', NULL)
                    ->where('text', NULL)
                    ->where('recursive', true)
                    ->where('root_attribute_id', NULL)
                    ->where('is_system', false)
                    ->where('multiple', false)
                    ->where('restrictions', NULL)
                    ->where('metadata', NULL)
                    ->where('entity_types_count', 1)
            )
            // This was originally an array keyed by index. But the backend changed
            // to use the id as key. Idk if this is a good idea. [SO]
            ->has('attributes.4.columns.6', fn($json) =>
                $json
                    ->where('id', 6)
                    ->where('thesaurus_url',  'https://spacialist.escience.uni-tuebingen.de/<user-project>/gefassposition#20171220105434')
                    ->where('datatype',  'string-sc')
                    ->where('thesaurus_root_url',  'https://spacialist.escience.uni-tuebingen.de/<user-project>/gefassposition#20171220105434')
                    ->where('created_at',  '2017-12-20T10:56:32.000000Z')
                    ->where('updated_at',  '2017-12-20T10:56:32.000000Z')
                    ->where('parent_id',  5)
                    ->where('text',  NULL)
                    ->where('recursive',  true)
                    ->where('root_attribute_id', NULL)
                    ->where('is_system', false)
                    ->where('multiple', false)
                    ->where('restrictions', NULL)
                    ->where('metadata', NULL)
            )
            ->has('attributes.4.columns.7', fn($json) =>
                $json
                    ->where('id', 7)
                    ->where('thesaurus_url', 'https://spacialist.escience.uni-tuebingen.de/<user-project>/verzierungselement#20171220105440')
                    ->where('datatype', 'string-sc')
                    ->where('thesaurus_root_url', 'https://spacialist.escience.uni-tuebingen.de/<user-project>/verzierungselement#20171220105440')
                    ->where('created_at', '2017-12-20T10:56:32.000000Z')
                    ->where('updated_at', '2017-12-20T10:56:32.000000Z')
                    ->where('parent_id', 5)
                    ->where('text', NULL)
                    ->where('recursive', true)
                    ->where('root_attribute_id', NULL)
                    ->where('is_system', false)
                    ->where('multiple', false)
                    ->where('restrictions', NULL)
                    ->where('metadata', NULL)
            )
            ->has('attributes.4.columns.8', fn($json) =>
                $json
                    ->where('id', 8)
                    ->where('thesaurus_url', 'https://spacialist.escience.uni-tuebingen.de/<user-project>/notizen#20171220105603')
                    ->where('datatype', 'string')
                    ->where('thesaurus_root_url', NULL)
                    ->where('created_at', '2017-12-20T10:56:32.000000Z')
                    ->where('updated_at', '2017-12-20T10:56:32.000000Z')
                    ->where('parent_id', 5)
                    ->where('text', NULL)
                    ->where('recursive', true)
                    ->where('root_attribute_id', NULL)
                    ->where('is_system', false)
                    ->where('multiple', false)
                    ->where('restrictions', NULL)
                    ->where('metadata', NULL)
                )
            ->has('selections')
    );
});

test('get attribute types endpoint', function () {
    $response = $this->userRequest()
        ->get('/api/v1/editor/dm/attribute_types');

    $attributeTypes = [
        "boolean"       => true,
        "date"          => true,
        "daterange"     => true,
        "dimension"     => false,
        "double"        => true,
        "string-mc"     => true,
        "string-sc"     => true,
        "entity"        => true,
        "entity-mc"     => true,
        "epoch"         => false,
        "geography"     => true,
        "iconclass"     => true,
        "integer"       => true,
        "list"          => false,
        "percentage"    => false,
        "richtext"      => false,
        "rism"          => true,
        "serial"        => false,
        "sql"           => false,
        "string"        => true,
        "stringf"       => false,
        "table"         => false,
        "timeperiod"    => true,
        "userlist"      => true,
        "url"           => true,
        "si-unit"       => true,
    ];

    $this->assertStatus($response, 200);
    $response->assertJsonCount(count($attributeTypes));
    $response->assertJsonStructure([
        '*' => [
            'datatype'
        ]
    ]);
    $resultArray = [];
    foreach($attributeTypes as $datatype => $in_table) {
        $resultArray[] = ['datatype' => $datatype, 'in_table' => $in_table];
    }

    $response->assertSimilarJson($resultArray);
});

test('get entity type endpoint', function () {
    $response = $this->userRequest()
        ->get('/api/v1/editor/entity_type/3');

    $this->assertStatus($response, 200);
    $response->assertJsonStructure([
        'id',
        'thesaurus_url',
        'is_root',
        'created_at',
        'updated_at',
        'sub_entity_types'
    ]);
    $response->assertJsonFragment([
        'id' => 3,
        'thesaurus_url' => 'https://spacialist.escience.uni-tuebingen.de/<user-project>/fundstelle#20171220094911',
        'is_root' => true,
        'created_at' => '2017-12-20T10:03:06.000000Z',
        'updated_at' => '2017-12-20T10:03:06.000000Z'
    ]);

    $content = json_decode($response->getContent());
    $subs = $content->sub_entity_types;
    expect($subs[0]->id)->toEqual(3);
    expect($subs[1]->id)->toEqual(4);
    expect($subs[2]->id)->toEqual(5);
    expect($subs[3]->id)->toEqual(6);
    expect($subs[4]->id)->toEqual(7);
});

test('add entity type endpoint', function () {
    $concept = ThConcept::first();

    $response = $this->userRequest()
        ->post('/api/v1/editor/dm/entity_type', [
            'concept_url' => $concept->concept_url,
            'is_root' => true,
            'geometry_type' => 'Any'
        ]);

    $entityType = EntityType::latest()->first();

    $this->assertStatus($response, 201);
    $response->assertJsonStructure([
        'id',
        'thesaurus_url',
        'is_root',
        'created_at',
        'updated_at',
        'color',
    ]);

    $response->assertJson(fn(AssertableJson $json) =>
        $json
            ->has('id')
            ->has('thesaurus_url')
            ->has('is_root')
            ->has('created_at')
            ->has('updated_at')
            ->has('color')
            ->where('id', $entityType->id)
            ->where('thesaurus_url', $concept->concept_url)
            ->where('is_root', true)
            ->where('created_at', $entityType->created_at->toJSON())
            ->where('updated_at', $entityType->updated_at->toJSON())
            ->where('color', $entityType->color)
    );

    // DISCUSS: Is this relevant anymore?
    // $entityTypeLayer = AvailableLayer::latest()->first();
    // $this->assertEquals($entityType->id, $entityTypeLayer->entity_type_id);
    // $layMax = AvailableLayer::where('is_overlay', true)->max('position');
    // $this->assertEquals($layMax+1, $entityTypeLayer->position);
});

test('add attribute endpoint', function () {
    $concept = ThConcept::first();
    $response = $this->userRequest()
        ->post('/api/v1/editor/dm/attribute', [
            'label_id' => $concept->id,
            'datatype' => 'string-sc',
            'root_id' => $concept->id,
            'recursive' => false
        ]);

    $attribute = Attribute::orderBy('id', 'desc')->first();
    $this->assertStatus($response, 201);
    $response->assertJsonStructure([
        'attribute' => [
            'id',
            'thesaurus_url',
            'datatype',
            'text',
            'thesaurus_root_url',
            'parent_id',
            'created_at',
            'updated_at',
            'recursive',
            'root_attribute_id'
        ],
        'selection'
    ]);

    $response->assertJson(fn(AssertableJson $json) =>
        $json
            ->has('attribute', fn($json) =>
                $json
                    ->has("id")
                    ->has('thesaurus_url')
                    ->has('datatype')
                    ->has('text')
                    ->has('thesaurus_root_url')
                    ->has('parent_id')
                    ->has('created_at')
                    ->has('updated_at')
                    ->has('recursive')
                    ->has('root_attribute_id')
                    ->has('is_system')
                    ->has('multiple')
                    ->has('restrictions')
                    ->has('metadata')
                    ->has('columns')
                    ->where('thesaurus_url', $concept->concept_url)
                    ->where('datatype', 'string-sc')
                    ->where('text', null)
                    ->where('thesaurus_root_url', $concept->concept_url)
                    ->where('parent_id', null)
                    ->where('created_at', $attribute->created_at->toJSON())
                    ->where('updated_at', $attribute->updated_at->toJSON())
                    ->where('recursive', false)
                    ->where('root_attribute_id', null)
                    ->where('is_system', false)
                    ->where('multiple', false)
                    ->where('restrictions', null)
                    ->where('metadata', null)
                    ->where('columns', [])
            )
            ->has('selection.0', fn($json) =>
                $json
                    ->has('id')
                    ->has('concept_scheme')
                    ->has('concept_url')
                    ->has('created_at')
                    ->has('updated_at')
                    ->has('is_top_concept')
                    ->has('narrower_id')
                    ->has('broader_id')
                    ->has('user_id')
                    ->where('id', 48)
            )
            ->has('selection.1', fn($json) =>
                $json
                    ->has('id')
                    ->has('concept_scheme')
                    ->has('concept_url')
                    ->has('created_at')
                    ->has('updated_at')
                    ->has('is_top_concept')
                    ->has('narrower_id')
                    ->has('broader_id')
                    ->has('user_id')
                    ->where('id', 62)
            )
    );
});

test('add attribute to entity type endpoint', function () {
    $response = $this->userRequest()
        ->post('/api/v1/editor/dm/entity_type/3/attribute', [
            'attribute_id' => 2,
            'position' => 1
        ]);

    $this->assertStatus($response, 201);
    $response->assertJsonStructure([
        'id',
        'entity_type_id',
        'attribute_id',
        'position',
        'depends_on',
        'created_at',
        'updated_at',
        'attribute' => [
            'id',
            'thesaurus_url',
            'datatype',
            'text',
            'thesaurus_root_url',
            'parent_id',
            'created_at',
            'updated_at',
            'recursive',
            'root_attribute_id'
        ]
    ]);
    $response->assertJson([
        'entity_type_id' => 3,
        'attribute_id' => 2,
        'position' => 1,
        'depends_on' => null,
        'attribute' => [
            'id' => 2,
            'thesaurus_url' => 'https://spacialist.escience.uni-tuebingen.de/<user-project>/erhaltung#20171220100437',
            'datatype' => 'percentage',
            'text' => null,
            'thesaurus_root_url' => null,
            'parent_id' => null,
            'recursive' => true,
            'root_attribute_id' => null
        ]
    ]);

    $response = $this->userRequest()
        ->post('/api/v1/editor/dm/entity_type/3/attribute', [
            'attribute_id' => 3
        ]);
    $this->assertStatus($response, 201);
    $response->assertJsonStructure([
        'id',
        'entity_type_id',
        'attribute_id',
        'position',
        'depends_on',
        'created_at',
        'updated_at',
        'attribute' => [
            'id',
            'thesaurus_url',
            'datatype',
            'text',
            'thesaurus_root_url',
            'parent_id',
            'created_at',
            'updated_at',
            'recursive',
            'root_attribute_id'
        ]
    ]);
    $response->assertJson([
        'entity_type_id' => 3,
        'attribute_id' => 3,
        'position' => 9,
        'depends_on' => null,
        'attribute' => [
            'id' => 3,
            'thesaurus_url' => 'https://spacialist.escience.uni-tuebingen.de/<user-project>/farbe#20171220100506',
            'datatype' => 'string-mc',
            'text' => null,
            'thesaurus_root_url' => null,
            'parent_id' => null,
            'recursive' => true,
            'root_attribute_id' => null
        ]
    ]);
});

test('duplicate entity type endpoint', function () {
    $entityType = EntityType::find(3)->load('sub_entity_types');
    $layMax = AvailableLayer::where('is_overlay', true)->max('position');
    $response = $this->userRequest()
        ->post('/api/v1/editor/dm/entity_type/3/duplicate');

    $newEntityType = EntityType::latest()->first();

    $this->assertStatus($response, 200);
    $response->assertJsonStructure([
        'id',
        'thesaurus_url',
        'is_root',
        'created_at',
        'updated_at',
        'sub_entity_types'
    ]);
    $response->assertJson([
        'id' => $newEntityType->id,
        'thesaurus_url' => $entityType->thesaurus_url,
        'is_root' => $entityType->is_root,
        'created_at' => $newEntityType->created_at->toJSON(),
        'updated_at' => $newEntityType->updated_at->toJSON()
    ]);

    $content = json_decode($response->getContent());
    $etSubTypeIds = $entityType->sub_entity_types->pluck('id');
    expect(count($content->sub_entity_types))->toEqual(count($etSubTypeIds));
});

test('edit entity type endpoint', function () {
    $entityType = EntityType::find(3);
    expect($entityType->thesaurus_url)->toEqual('https://spacialist.escience.uni-tuebingen.de/<user-project>/fundstelle#20171220094911');

    $response = $this->userRequest()
        ->patch('/api/v1/editor/dm/entity_type/3', [
            'data' => [
                'thesaurus_url' => 'https://spacialist.escience.uni-tuebingen.de/<user-project>/erhaltung#20171220100437'
            ]
        ]);

    $this->assertStatus($response, 200);

    $entityType = EntityType::find(3);
    expect($entityType->thesaurus_url)->toEqual('https://spacialist.escience.uni-tuebingen.de/<user-project>/erhaltung#20171220100437');
});

test('roorder entity type attributes endpoint', function () {
    $entityType = EntityType::find(3)->load('attributes');
    foreach($entityType->attributes as $a) {
        if($a->attribute_id == 14) {
            expect($a->pivot->position)->toEqual(1);
        } else if($a->attribute_id == 16) {
            expect($a->pivot->position)->toEqual(2);
        } else if($a->attribute_id == 17) {
            expect($a->pivot->position)->toEqual(3);
        } else if($a->attribute_id == 10) {
            expect($a->pivot->position)->toEqual(4);
        } else if($a->attribute_id == 13) {
            expect($a->pivot->position)->toEqual(5);
        }
    }

    $response = $this->userRequest()
        ->patch('/api/v1/editor/dm/entity_type/4/attribute/14/position', [
            'position' => 4
        ]);

    $this->assertStatus($response, 204);

    $entityType = EntityType::find(3)->load('attributes');
    foreach($entityType->attributes as $a) {
        if($a->attribute_id == 14) {
            expect($a->pivot->position)->toEqual(4);
        } else if($a->attribute_id == 16) {
            expect($a->pivot->position)->toEqual(1);
        } else if($a->attribute_id == 17) {
            expect($a->pivot->position)->toEqual(2);
        } else if($a->attribute_id == 10) {
            expect($a->pivot->position)->toEqual(3);
        } else if($a->attribute_id == 13) {
            expect($a->pivot->position)->toEqual(5);
        }
    }

    // Testing again with higher position
    $response = $this->userRequest()
        ->patch('/api/v1/editor/dm/entity_type/4/attribute/13/position', [
            'position' => 1
        ]);

    $this->assertStatus($response, 204);

    $entityType = EntityType::find(3)->load('attributes');
    foreach($entityType->attributes as $a) {
        if($a->attribute_id == 14) {
            expect($a->pivot->position)->toEqual(5);
        } else if($a->attribute_id == 16) {
            expect($a->pivot->position)->toEqual(2);
        } else if($a->attribute_id == 17) {
            expect($a->pivot->position)->toEqual(3);
        } else if($a->attribute_id == 10) {
            expect($a->pivot->position)->toEqual(4);
        } else if($a->attribute_id == 13) {
            expect($a->pivot->position)->toEqual(1);
        }
    }

    // Testing again with same position (nothing should happen)
    $response = $this->userRequest()
        ->patch('/api/v1/editor/dm/entity_type/4/attribute/14/position', [
            'position' => 4
        ]);

    $this->assertStatus($response, 204);

    $entityType = EntityType::find(3)->load('attributes');
    foreach($entityType->attributes as $a) {
        if($a->attribute_id == 14) {
            expect($a->pivot->position)->toEqual(4);
        } else if($a->attribute_id == 16) {
            expect($a->pivot->position)->toEqual(1);
        } else if($a->attribute_id == 17) {
            expect($a->pivot->position)->toEqual(2);
        } else if($a->attribute_id == 10) {
            expect($a->pivot->position)->toEqual(3);
        } else if($a->attribute_id == 13) {
            expect($a->pivot->position)->toEqual(5);
        }
    }
});

test('add dependency to entiy type attribute endpoint', function () {
    $response = $this->userRequest()
        ->patch('/api/v1/editor/dm/entity_type/4/attribute/14/dependency', [
            'data' => [
                'or' => false,
                'groups' => [
                    [
                        'or' => true,
                        'rules' => [
                            [
                                'attribute' => 13,
                                'operator' => '=',
                                'value' => 'Test Value'
                            ],
                        ],
                    ]
                ],
            ],
        ]);

    $this->assertStatus($response, 200);

    $entityAttribute = EntityAttribute::for(4, 14);
    expect($entityAttribute)->toHaveKey('depends_on');
    expect($entityAttribute->depends_on)->toEqual([
        'or' => false,
        'groups' => [
            [
                'or' => true,
                'rules' => [
                    [
                        'operator' => '=',
                        'value' => 'Test Value',
                        'on' => 13,
                    ],
                ],
            ]
        ],
    ]);
});

test('add empty dependency to entiy type attribute endpoint', function () {
    $response = $this->userRequest()
        ->patch('/api/v1/editor/dm/entity_type/4/attribute/14/dependency', [
            'data' => [
                'or' => false,
                'groups' => [],
            ],
        ]);

    $this->assertStatus($response, 200);

    $entityAttribute = EntityAttribute::for(4, 14);
    expect($entityAttribute->depends_on)->toBeNull();
});

test('delete entity type endpoint', function () {
    $etCnt = EntityType::count();
    expect($etCnt)->toEqual(5);
    $eaCnt = EntityAttribute::count();
    expect($eaCnt)->toEqual(29);
    $eCnt = Entity::count();
    expect($eCnt)->toEqual(8);
    $avCnt = AttributeValue::count();
    expect($avCnt)->toEqual(31);

    $response = $this->userRequest()
        ->delete('/api/v1/editor/dm/entity_type/4');

    $this->assertStatus($response, 204);

    $etCnt = EntityType::count();
    expect($etCnt)->toEqual(4);
    $eaCnt = EntityAttribute::count();
    expect($eaCnt)->toEqual(23);
    $eCnt = Entity::count();
    expect($eCnt)->toEqual(4);
    $avCnt = AttributeValue::count();
    expect($avCnt)->toEqual(11);
});

test('delete attribute endpoint', function () {
    $eaCnt = EntityAttribute::count();
    expect($eaCnt)->toEqual(29);
    $avCnt = AttributeValue::count();
    expect($avCnt)->toEqual(31);
    $aCnt = Attribute::count();
    expect($aCnt)->toEqual(24);

    $response = $this->userRequest()
        ->delete('/api/v1/editor/dm/attribute/12');

    $this->assertStatus($response, 204);

    $eaCnt = EntityAttribute::count();
    expect($eaCnt)->toEqual(27);
    $avCnt = AttributeValue::count();
    expect($avCnt)->toEqual(27);
    $aCnt = Attribute::count();
    expect($aCnt)->toEqual(23);
});

test('delete attribute from entity type endpoint', function () {
    $eaCnt = EntityAttribute::count();
    expect($eaCnt)->toEqual(29);
    $avCnt = AttributeValue::count();
    expect($avCnt)->toEqual(31);
    $entityType = EntityType::find(5)->load('attributes');

    $oldPositions = [12, 9, 11, 2, 3, 5, 19, 4, 13];
    foreach($entityType->attributes as $entityType) {
        foreach($oldPositions as $index => $position) {
            if($entityType->id == $position) {
                expect($entityType->pivot->position)->toEqual($index+1);
                break;
            }
        }
    }

    $entityAttribute = EntityAttribute::for(5, 11);
    $response = $this->userRequest()
        ->delete("/api/v1/editor/dm/entity_type/attribute/$entityAttribute->id");

    $this->assertStatus($response, 204);

    $eaCnt = EntityAttribute::count();
    expect($eaCnt)->toEqual(28);
    $avCnt = AttributeValue::count();
    expect($avCnt)->toEqual(28);
    $entityType = EntityType::find(5)->load('attributes');
    $newPositions = [12, 9, 2, 3, 5, 19, 4, 13];
    foreach($entityType->attributes as $entityType) {
        foreach($newPositions as $index => $position) {
            if($entityType->id == $position) {
                expect($entityType->pivot->position)->toEqual($index+1);
                break;
            }
        }
    }
});

test('without permission', function ($permission) {
    (new ResponseTester($this))->testMissingPermission($permission);
})->with('permissionsProvider');

test('exceptions', function ($permission) {
    (new ResponseTester($this))->testExceptions($permission);
})->with('exceptionsProvider');

dataset('permissionsProvider', function () {
    return [
        'permission to get entity type'                        => Permission::for("get", "/api/v1/editor/entity_type/1",           "You do not have the permission to get an entity type's data"),
        'permission to view entity data on top entity types'   => Permission::for("get", "/api/v1/editor/dm/entity_type/top",      "You do not have the permission to view entity data"),
        'permission to view entity data'                       => Permission::for("get", "/api/v1/editor/dm/attribute",           "You do not have the permission to view entity data"),
        'permission to create entity type'                     => Permission::for("post", "/api/v1/editor/dm/entity_type",        "You do not have the permission to create a new entity type"),
        // TODO check if necessary to replace old set relation logic with new logic in EditorController::patchEntityType
        // 'permission to modify entity relations'                => Permission::for("post", "/api/v1/editor/dm/1/relation",     "You do not have the permission to modify entity relations"),
        'permission to view entity data'                       => Permission::for("get", "/api/v1/editor/entity_type/1/attribute", "You do not have the permission to view entity data"),
        'permission to view entity data on top entity types'   => Permission::for("get", "/api/v1/editor/dm/entity_type/top",      "You do not have the permission to view entity data"),
        'permission to view entity data'                       => Permission::for("get", "/api/v1/editor/dm/attribute",           "You do not have the permission to view entity data"),
        'permission to create entity type'                     => Permission::for("post", "/api/v1/editor/dm/entity_type",        "You do not have the permission to create a new entity type"),
        'permission to add attributes'                         => Permission::for("post", "/api/v1/editor/dm/attribute",          "You do not have the permission to add attributes",[
                'label_id' => 1,
                'datatype' => 'string-sc',
                'root_id' => 1,
                'recursive' => false
        ]),
        'permission to add attributes to an entity type'    => Permission::for("post", "/api/v1/editor/dm/entity_type/1/attribute", "You do not have the permission to add attributes to an entity type"),
        'permission to duplicate an entity type'            => Permission::for("post", "/api/v1/editor/dm/entity_type/1/duplicate", "You do not have the permission to duplicate an entity type"),
        'permission to modify entity-type'                  => Permission::for("patch", "/api/v1/editor/dm/entity_type/1", "You do not have the permission to modify entity-type",[
            'data' => [
                'thesaurus_url' => 'https://spacialist.escience.uni-tuebingen.de/<user-project>/fundstelle#20171220094911'
            ]
        ]),
        'permission to reorder attributes'                     => Permission::for("patch", "/api/v1/editor/dm/entity_type/1/attribute/1/position", "You do not have the permission to reorder attributes"),
        'permission to add/modify attribute dependencies'      => Permission::for("patch", "/api/v1/editor/dm/entity_type/1/attribute/1/dependency", "You do not have the permission to add/modify attribute dependencies", [
                'data' => [
                    'or' => false,
                    'groups' => [
                        [
                            'or' => true,
                            'rules' => [
                                [
                                    'attribute' => 15,
                                    'operator' => '=',
                                    'value' => 'NoValue',
                                ],
                            ],
                        ]
                    ],
                ],
            ]),
        'permission to edit metadata of an attribute'                    => Permission::for("patch", "/api/v1/editor/dm/entity_type/attribute/1/metadata", "You do not have the permission to edit attribute names"),
        'permission to delete entity types'                    => Permission::for("delete", "/api/v1/editor/dm/entity_type/1", "You do not have the permission to delete entity types"),
        'permission to delete attributes'                      => Permission::for("delete", "/api/v1/editor/dm/attribute/1", "You do not have the permission to delete attributes"),
        'permission to remove attributes from entity types'    => Permission::for("delete", "/api/v1/editor/dm/entity_type/attribute/19", "You do not have the permission to remove attributes from entity types"),
    ];
});

dataset('exceptionsProvider', function () {
    $entityDoesNotExist = "This entity-type does not exist";
    $entityAttributeNotFound = "Entity Attribute not found";
    $attributeDoesNotExist = "This attribute does not exist";
    $attributeAlreadyAdded = "This attribute already exists on this entity-type";

    return [
        'exception on get entity type'                         => Permission::for("get", "/api/v1/editor/entity_type/99", $entityDoesNotExist),
        'exception on view entity data'                        => Permission::for("post", "/api/v1/editor/dm/entity_type/99/attribute", $entityDoesNotExist,[
            'attribute_id' => 2,
            'position' => 1
        ]),
        // TODO check if necessary to replace old set relation logic with new logic in EditorController::patchEntityType
        // 'exception on modify entity relations'                 => Permission::for("post", "/api/v1/editor/dm/99/relation", $entityDoesNotExist),
        'exception on add attributes to an entity type'        => Permission::for("post", "/api/v1/editor/dm/entity_type/99/attribute", $entityDoesNotExist,[
            'attribute_id' => 2,
            'position' => 1
        ]),
        'exception on add already added attribute'             => Permission::for("post", "/api/v1/editor/dm/entity_type/3/attribute", $attributeAlreadyAdded,[
            'attribute_id' => 15,
            'position' => 1
        ]),
        'exception on duplicate an entity type'                => Permission::for("post", "/api/v1/editor/dm/entity_type/99/duplicate", $entityDoesNotExist),
        'exception on modify entity-type'                      => Permission::for("patch", "/api/v1/editor/dm/entity_type/99", $entityDoesNotExist,[
            'data' => [
                'thesaurus_url' => 'https://spacialist.escience.uni-tuebingen.de/<user-project>/fundstelle#20171220094911'
            ]
        ]),
        'exception on reorder attributes'                      => Permission::for("patch", "/api/v1/editor/dm/entity_type/1/attribute/99/position", $entityAttributeNotFound, [
            'position' => 1
        ]),
        'exception on add/modify attribute dependencies'       => Permission::for("patch", "/api/v1/editor/dm/entity_type/1/attribute/99/dependency", $entityAttributeNotFound, [
                'data' => [
                    'or' => false,
                    'groups' => [
                        [
                            'or' => true,
                            'rules' => [
                                [
                                    'attribute' => 15,
                                    'operator' => '=',
                                    'value' => 'NoValue',
                                ],
                            ],
                        ]
                    ],
                ]
            ]),
        'exception on add/modify attribute dependencies with wrong reference'       => Permission::for("patch", "/api/v1/editor/dm/entity_type/3/attribute/15/dependency", 'Entity attribute does not exist', [
                'data' => [
                    'or' => false,
                    'groups' => [
                        [
                            'or' => true,
                            'rules' => [
                                [
                                    'attribute' => 99,
                                    'operator' => '=',
                                    'value' => 'NoValue',
                                ],
                            ],
                        ]
                    ],
                ]
            ]),
        'exception on add/modify attribute dependencies with operator mismatch'       => Permission::for("patch", "/api/v1/editor/dm/entity_type/3/attribute/15/dependency", 'Operator mismatch', [
                'data' => [
                    'or' => false,
                    'groups' => [
                        [
                            'or' => true,
                            'rules' => [
                                [
                                    'attribute' => 15,
                                    'operator' => 'INVALID',
                                    'value' => 'NoValue',
                                ],
                            ],
                        ]
                    ],
                ]
            ]),
        'exception on delete entity types'                     => Permission::for("delete", "/api/v1/editor/dm/entity_type/99", $entityDoesNotExist),
        'exception on delete attributes'                       => Permission::for("delete", "/api/v1/editor/dm/attribute/99", $attributeDoesNotExist),
        'exception on remove attributes from entity types'     => Permission::for("delete", "/api/v1/editor/dm/entity_type/attribute/99", $entityAttributeNotFound),
    ];
});
