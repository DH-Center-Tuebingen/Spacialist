<?php

namespace Tests\Feature;
use Tests\TestCase;

use App\AttributeValue;
use App\Entity;
use App\User;
use Tests\Permission;
use Tests\ResponseTester;

class ApiEntityTest extends TestCase
{
    // ==========================================
    //              [[ GET ]]
    // ==========================================

    /**
     * @testdox GET    /api/v1/entity  -  Get all top entities.
     */
    public function testTopEntityEndpoint()
    {
        $response = $this->userRequest()
            ->get('/api/v1/entity/top');

        $response->assertJsonCount(3);
        $response->assertJsonStructure([
            [
                'id',
                'name',
                'entity_type_id',
                'root_entity_id',
                'rank',
                'user_id',
                'created_at',
                'updated_at',
                'parentIds',
                'parentNames'
            ]
        ]);
    }

    /**
     * @testdox GET    /api/v1/entity/{id}  -  Get entity (id=1).
     */
    public function testEntityEndpointtestEntityEndpoint()
    {
        $response = $this->userRequest()
            ->get('/api/v1/entity/1');

        $response->assertSimilarJson([
            'id' => 1,
            'name' => 'Site A',
            'entity_type_id' => 3,
            'root_entity_id' => null,
            'rank' => 1,
            'user_id' => 1,
            'user' => [
                'id' => 1,
                'name' => "Admin",
                'nickname' => "admin",
                'email' => "admin@localhost",
                'created_at' => "2017-12-20T09:47:36.000000Z",
                'updated_at' => "2017-12-20T09:47:36.000000Z",
                'deleted_at' => null,
                'avatar' => null,
                'avatar_url' => null,
                'metadata' => null,
                'login_attempts' => null,
            ],
            'created_at' => '2017-12-20T17:10:34.000000Z',
            'updated_at' => '2017-12-31T16:10:56.000000Z',
            'parentIds' => [1],
            'parentNames' => ['Site A'],
            'comments_count' => 3,
            'metadata' => [],
            'attributeLinks' => [],
        ]);
    }

    /**
     * @testdox GET    /api/v1/entity/{id}  -  Get entity (id=2).
     */
    public function testEntityEndpointId()
    {
        $response = $this->userRequest()
            ->get('/api/v1/entity/2');

        $response->assertJson([
            'id' => 2,
        ]);
    }

    /**
     * @testdox GET    /api/v1/entity/{id}  -  Get non-existing entity.
     */
    public function testEntityWrongIdEndpoint()
    {
        $response = $this->userRequest()
            ->get('/api/v1/entity/-1');

        $response->assertStatus(404);
    }

    /**
     * @testdox GET    /api/v1/entity/entity_type/{entity_id}/data/{attribute_id}  -  Get attribute values (id=15) of an entity-type (id=3).
     */
    public function testEntityTypeDataEndpoint()
    {
        $response = $this->userRequest()
            ->get('/api/v1/entity/entity_type/3/data/15');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'entity_id',
                'attribute_id',
                'value',
                'user_id',
                'created_at',
                'updated_at'
            ]
        ]);
        $response->assertJson([
            1 => [
                'value' => ['Fundstelle A']
            ],
            7 => [
                'value' => ['Kirchberg']
            ],
        ]);
    }

     /**
      * @testdox GET    /api/v1/entity/{id}/data  -  Get data of an entity (id=1).
      */
    public function testEntityDataEndpoint()
    {
        $response = $this->userRequest()
            ->get('/api/v1/entity/1/data');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonStructure([
            15 => [
                'id',
                'entity_id',
                'attribute_id',
                'value',
                'user_id',
                'created_at',
                'updated_at'
            ]
        ]);
        $response->assertJson([
            15 => [
                'value' => ['Fundstelle A']
            ]
        ]);
    }

     /**
      * @testdox GET    /api/v1/entity/{id}/data  -  Get data of a non-existing entity.
      */
    public function testEntityDataWithWrongIdEndpoint()
    {
        $response = $this->userRequest()
            ->get('/api/v1/entity/99/data');

        $response->assertStatus(400);
        $response->assertSimilarJson([
            'error' => 'This entity does not exist'
        ]);
    }

    /**
     * @testdox GET    /api/v1/entity/{id}/data/{aid}  -  Get data of an attribute (id=15) of an entity (id=1).
     */
    public function testEntityDataWithAttributeEndpoint()
    {
        $response = $this->userRequest()
            ->get('/api/v1/entity/1/data/15');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonStructure([
            15 => [
                'id',
                'entity_id',
                'attribute_id',
                'value',
                'user_id',
                'created_at',
                'updated_at'
            ]
        ]);
        $response->assertJson([
            15 => [
                'value' => ['Fundstelle A']
            ]
        ]);
    }

     /**
      * @testdox GET    /api/v1/entity/{id}/data/{aid}  -  Get data of a non-existing attribute (id=99) of an entity (id=1).
      */
    public function testEntityDataWithWrongAttributeEndpoint()
    {
        $response = $this->userRequest()
            ->get('/api/v1/entity/1/data/99');

        $response->assertStatus(400);
        $response->assertSimilarJson([
            'error' => 'This attribute does not exist'
        ]);
    }

    /**
    * @testdox GET    /api/v1/entity/{id}/parentIds  -  Get all parentIds of an entity (id=5).
     */
    public function testEntityParentIdEndpoint()
    {
        $response = $this->userRequest()
            ->get('/api/v1/entity/5/parentIds');

        $response->assertJsonCount(3);
        $response->assertSimilarJson([
            1, 2, 5
        ]);
    }


    /**
     * @testdox GET    /api/v1/entity/byParent/{id}  -  Get all sub-entities/children of an entity (id=2).
     */
    public function testEntityParentEndpoint()
    {
        $response = $this->userRequest()
            ->get('/api/v1/entity/byParent/2');

        $response->assertJsonCount(3);
        $response->assertJsonStructure([
            [
                'id',
                'name',
                'entity_type_id',
                'root_entity_id',
                'rank',
                'user_id',
                'created_at',
                'updated_at',
                'parentIds'
            ]
        ]);
        $response->assertJson([
            ['id' => 3],
            ['id' => 4],
            ['id' => 5],
        ]);
    }

    // ==========================================
    //              [[ POST ]]
    // ==========================================

    /**
     * @testdox POST   /api/v1/entity  -  Add a new entity.
     */
    public function testNewEntityEndpoint()
    {
        $cnt = Entity::count();
        $this->assertEquals($cnt, 8);

        $response = $this->userRequest()
        ->post('/api/v1/entity', [
            'name' => 'Unit-Test Entity I',
            'entity_type_id' => 3,
            'root_entity_id' => 1
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id',
            'name',
            'entity_type_id',
            'root_entity_id',
            'rank',
            'user_id',
            'created_at',
            'updated_at',
            'parentIds'
        ]);
        $response->assertJson([
            'name' => 'Unit-Test Entity I',
            'entity_type_id' => 3,
            'root_entity_id' => 1,
            'rank' => 2,
        ]);

        $cnt = Entity::count();
        $this->assertEquals($cnt, 9);
    }

    /**
     *  @testdox POST   /api/v1/entity  -  Add a new root entity.
     */
    public function testNewRootEntityEndpoint()
    {
        $cnt = Entity::count();
        $this->assertEquals($cnt, 8);

        $response = $this->userRequest()
        ->post('/api/v1/entity', [
            'name' => 'Unit-Test Entity I',
            'entity_type_id' => 3,
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id',
            'name',
            'entity_type_id',
            'root_entity_id',
            'rank',
            'user_id',
            'created_at',
            'updated_at',
            'parentIds'
        ]);
        $response->assertJson([
            'name' => 'Unit-Test Entity I',
            'entity_type_id' => 3,
            'root_entity_id' => null,
            'rank' => 4,
        ]);

        $cnt = Entity::count();
        $this->assertEquals($cnt, 9);
    }


    // ==========================================
    //              [[ PATCH ]]
    // ==========================================

    /**
     * @testdox PATCH  /api/v1/entity/{entity_id}/attributes  -  Test modifying [remove, replace, add] attributes of an entity (id=4).
     */
    public function testPatchAttributesEndpoint()
    {
        $entity = Entity::with('attributes')->find(4);

        foreach($entity->attributes as $attr) {
            if($attr->id == 11) {
                $this->assertEquals(10, $attr->pivot->int_val);
            }
            else if($attr->id == 9) {
                $val = json_decode($attr->pivot->json_val);
                $this->assertEquals(45, $val->B);
                $this->assertEquals(40, $val->H);
                $this->assertEquals('cm', $val->unit);
            }
        }

        $response = $this->userRequest()
            ->patch('/api/v1/entity/4/attributes', [
                [
                    'params' => [
                        'id' => 39,
                        'aid' => 9,
                        'cid' => 4
                    ],
                    'op' => 'remove'
                ],
                [
                    'params' => [
                        'id' => 37,
                        'aid' => 11,
                        'cid' => 4
                    ],
                    'op' => 'replace',
                    'value' => 2
                ],
                [
                    'params' => [
                        'aid' => 19,
                        'cid' => 4
                    ],
                    'op' => 'add',
                    'value' => 'Test'
                ]
            ]);

        $response->assertStatus(200);
        $response->assertJson( fn($json) =>
            $json
                ->has('entity', fn($entityJson) =>
                    $entityJson
                        ->has('id')
                        ->has('name')
                        ->has('entity_type_id')
                        ->has('root_entity_id')
                        ->has('rank')
                        ->has('user_id')
                        ->has('created_at')
                        ->has('updated_at')
                        ->has('parentIds')
                        ->etc()
                )
                ->has('added_attributes.19', fn($addedAttrJson) =>
                    $addedAttrJson
                        ->has('id')
                        ->has('entity_id')
                        ->has('attribute_id')
                        ->has('certainty')
                        ->has('user_id')
                        ->has('created_at')
                        ->has('updated_at')
                        ->etc()
                )
                ->has('removed_attributes.9', fn($removedAttrJson) =>
                    $removedAttrJson
                        ->has('id')
                        ->has('entity_id')
                        ->has('attribute_id')
                        ->has('certainty')
                        ->has('user_id')
                        ->has('created_at')
                        ->has('updated_at')
                        ->etc()
                )
        );

        $entity = Entity::with('attributes')->find(4);
        foreach($entity->attributes as $attr) {
            if($attr->id == 37) {
                $this->assertEquals(2, $attr->value);
            }
            else if($attr->id == 39) {
                $this->assertTrue(false);
            } else if($attr->attribute_id == 19) {
                $this->assertEquals('Test', $attr->value);
            }
        }
    }

    /**
     * @testdox PATCH  /api/v1/entity/{entity_id}/attributes  -  Test setting wrong values for epoch attribute of an entity (id=2).
     */
    public function testPatchWrongAttributesEndpoint()
    {
        $entity = Entity::with('attributes')->find(2);

        foreach($entity->attributes as $attr) {
            if($attr->id == 17) {
                $val = json_decode($attr->pivot->json_val);
                $this->assertEquals(340, $val->start);
                $this->assertEquals(300, $val->end);
                $this->assertEquals('bc', $val->startLabel);
                $this->assertEquals('bc', $val->endLabel);
            }
        }

        $response = $this->userRequest()
        ->patch('/api/v1/entity/2/attributes', [
            [
                'params' => [
                    'id' => 62,
                    'aid' => 17,
                    'cid' => 2
                ],
                'op' => 'replace',
                'value' => [
                    'startLabel' => 'ad',
                    'endLabel' => 'bc',
                    'start' => 400,
                    'end' => 150,
                    'epoch' => [
                        'concept_url' => 'https://spacialist.escience.uni-tuebingen.de/<user-project>/eisenzeit#20171220165409'
                    ]
                ]
            ],
        ]);

        $response->assertStatus(422);
        $response->assertSimilarJson([
            'error' => 'Start date of a time period must not be after it\'s end date'
        ]);

        $response = $this->userRequest()
        ->patch('/api/v1/entity/2/attributes', [
            [
                'params' => [
                    'id' => 62,
                    'aid' => 17,
                    'cid' => 2
                ],
                'op' => 'replace',
                'value' => [
                    'startLabel' => 'ad',
                    'endLabel' => 'ad',
                    'start' => 400,
                    'end' => 150,
                    'epoch' => [
                        'concept_url' => 'https://spacialist.escience.uni-tuebingen.de/<user-project>/eisenzeit#20171220165409'
                    ]
                ]
            ],
        ]);

        $response->assertStatus(422);
        $response->assertSimilarJson([
            'error' => 'Start date of a time period must not be after it\'s end date'
        ]);

        $response = $this->userRequest()
        ->patch('/api/v1/entity/2/attributes', [
            [
                'params' => [
                    'id' => 62,
                    'aid' => 17,
                    'cid' => 2
                ],
                'op' => 'replace',
                'value' => [
                    'startLabel' => 'bc',
                    'endLabel' => 'bc',
                    'start' => 100,
                    'end' => 150,
                    'epoch' => [
                        'concept_url' => 'https://spacialist.escience.uni-tuebingen.de/<user-project>/eisenzeit#20171220165409'
                    ]
                ]
            ],
        ]);

        $response->assertStatus(422);
        $response->assertSimilarJson([
            'error' => 'Start date of a time period must not be after it\'s end date'
        ]);

        $response = $this->userRequest()
        ->patch('/api/v1/entity/2/attributes', [
            [
                'params' => [
                    'id' => 62,
                    'aid' => 17,
                    'cid' => 2
                ],
                'op' => 'replace',
                'value' => [
                    'startLabel' => 'bc',
                    'endLabel' => 'bc',
                    'start' => 400,
                    'end' => 300,
                    'epoch' => [
                        'concept_url' => 'https://spacialist.escience.uni-tuebingen.de/<user-project>/eisenzeit#20171220165409'
                    ]
                ]
            ],
        ]);

        $response->assertStatus(200);

        $entity = Entity::with('attributes')->find(2);
        foreach($entity->attributes as $attr) {
            if($attr->id == 17) {
                $val = json_decode($attr->pivot->json_val);
                $this->assertEquals(400, $val->start);
                $this->assertEquals(300, $val->end);
                $this->assertEquals('bc', $val->startLabel);
                $this->assertEquals('bc', $val->endLabel);
            }
        }
    }

    /**
     * @testdox PATCH  /api/v1/entity/{entity_id}/name  -  Test renaming an entity (id=1)  -  Site A => Site A_renamed.
     */
    public function testPatchRenameEntityEndpoint()
    {
        $entity = Entity::find(1);
        $this->assertEquals('Site A', $entity->name);

        $response = $this->userRequest()
        ->patch('/api/v1/entity/1/name', [
            'name' => 'Site A_renamed'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'name',
            'entity_type_id',
            'root_entity_id',
            'rank',
            'user_id',
            'created_at',
            'updated_at',
            'user',
        ]);

        $entity = Entity::find(1);
        $this->assertEquals('Site A_renamed', $entity->name);
    }

    /**
     * @testdox PATCH  /api/v1/entity/{entity_id}/rank  -  Move an entity (id=1) from one parent (id=7) to another entity (id=8).
     */
    public function testPatchMoveEntityEndpoint()
    {
        $siteA = Entity::find(1);
        $this->assertEquals('Site A', $siteA->name);
        $this->assertNull($siteA->root_entity_id);

        $siteB = Entity::find(7);
        $this->assertEquals('Site B', $siteB->name);
        $this->assertNull($siteB->root_entity_id);
        $this->assertEquals(2, $siteB->rank);

        $find12 = Entity::find(8);
        $this->assertEquals('Fund 12', $find12->name);
        $this->assertEquals(1, $find12->rank);
        $this->assertEquals(7, $find12->root_entity_id);

        $response = $this->userRequest()
            ->patch('/api/v1/entity/1/rank', [
                'rank' => 1,
                'parent_id' => 7
            ]);

        $response->assertStatus(204);

        $siteA = Entity::find(1);
        $siteB = Entity::find(7);
        $find12 = Entity::find(8);
        // Changed values
        $this->assertEquals(7, $siteA->root_entity_id);
        $this->assertEquals(1, $siteA->rank);

        // Same values
        $this->assertEquals(1, $siteB->rank);
        $this->assertEquals(null, $siteB->root_entity_id);
        $this->assertEquals(7, $find12->root_entity_id);
        $this->assertEquals(2, $find12->rank);
    }


    /**
     *  @dataProvider  moveExceptionsProvider
     *  @testdox PATCH  /api/v1/entity/{entity_id}/rank  -  Move entities exception
     */
    function testMoveExceptions(int $entity,int | null $newParentEntity, int $statusCode = 400) {
            $response = $this->userRequest()
            ->patch("/api/v1/entity/$entity/rank", [
                'rank' => 1,
                'parent_id' => $newParentEntity
            ]);

            $response->assertStatus($statusCode);
    }

    public static function moveExceptionsProvider(): array{
        return [
            "invalid entity type to top" => [8, null],
            "invalid entity type to incompatible parent" => [7, 2],
            "invalid entity type to child" => [1, 2],
            "invalid entity type to self" => [8, 8],
            "move to non-existing entity" => [8, 99999, 422],
        ];
    }




    // ==========================================
    //              [[ DELETE ]]
    // ==========================================

    /**
     * @testdox DELETE /api/v1/entity/{entity_id}  -  Delete an entity (id=8) with no sub-entities.
     */
    public function testDeleteEntityWithoutSubEntities()
    {
        $cnt = Entity::count();
        $this->assertEquals($cnt, 8);

        $response = $this->userRequest()
            ->delete('/api/v1/entity/8');

        $response->assertStatus(204);

        $cnt = Entity::count();
        $this->assertEquals($cnt, 7);
    }

    /**
     * @testdox DELETE /api/v1/entity/{entity_id}  -  Delete an entity (id=1) and all it's sub-entities.
     */
    public function testDeleteEntityEndpoint()
    {
        $cnt = Entity::count();
        $this->assertEquals($cnt, 8);

        $response = $this->userRequest()
            ->delete('/api/v1/entity/1');

        $response->assertStatus(204);

        $cnt = Entity::count();
        $this->assertEquals($cnt, 3);
    }

    /**
     * @testdox DELETE /api/v1/entity/{entity_id}  -  Delete a non-existing entity.
     */
    public function testDeleteEntityWrongIdEndpoint()
    {
        $response = $this->userRequest()
        ->delete('/api/v1/entity/99');

        $response->assertStatus(400);
        $response->assertSimilarJson([
            'error' => 'This entity does not exist'
        ]);
    }

    // ==========================================
    //      [[ ADDITIONAL DATA PROVIDERS ]]
    // ==========================================

    /**
     * @dataProvider permissions
     * @testdox [[PROVIDER]] Routes Without Permissions
     */
    public function testWithoutPermission($permission) {
        (new ResponseTester($this))->testMissingPermission($permission);
    }
    /**
     * @dataProvider exceptions
     * @testdox [[PROVIDER]] Exceptions With Permissions
     */
    public function testSucceedWithPermission($permission) {
        (new ResponseTester($this))->testExceptions($permission);
    }

    public static function permissions() {
        return [
            "GET    /api/v1/entity/top"                    => Permission::for("get", "/api/v1/entity/top", "You do not have the permission to get entities"),
            "GET    /api/v1/entity/1"                      => Permission::for("get", "/api/v1/entity/1", "You do not have the permission to get a specific entity"),
            "GET    /api/v1/entity/entity_type/3/data/14"  => Permission::for("get", "/api/v1/entity/entity_type/3/data/14", "You do not have the permission to get an entity's data"),
            "GET    /api/v1/entity/1/data/14"              => Permission::for("get", "/api/v1/entity/1/data/14", "You do not have the permission to get an entity's data"),
            "GET    /api/v1/entity/1/parentIds"            => Permission::for("get", "/api/v1/entity/1/parentIds", "You do not have the permission to get an entity's parent id's"),
            "POST   /api/v1/entity"                        => Permission::for("post", "/api/v1/entity", "You do not have the permission to add a new entity"),
            "PATCH  /api/v1/entity/1/attributes"           => Permission::for("patch", "/api/v1/entity/1/attributes", "You do not have the permission to modify an entity's data"),
            "PATCH  /api/v1/entity/1/attribute/13"         => Permission::for("patch", "/api/v1/entity/1/attribute/13", "You do not have the permission to modify an entity's data"),
            "PATCH  /api/v1/entity/1/name"                 => Permission::for("patch", "/api/v1/entity/1/name", "You do not have the permission to modify an entity's data"),
            "PATCH  /api/v1/entity/1/rank"                 => Permission::for("patch", "/api/v1/entity/1/rank", "You do not have the permission to modify an entity"),
            "DELETE /api/v1/entity/1"                      => Permission::for("delete", "/api/v1/entity/1", "You do not have the permission to delete an entity"),
        ];
    }

    public static function exceptions() {
        return [
            "GET    /api/v1/entity/99" => Permission::for("get", "/api/v1/entity/99", "This entity does not exist"),
            "GET    /api/v1/entity/entity_type/99/data/14" => Permission::for("get", "/api/v1/entity/entity_type/99/data/14", "This entity type does not exist"),
            "GET    /api/v1/entity/entity_type/3/data/99" => Permission::for("get", "/api/v1/entity/entity_type/3/data/99", "This attribute does not exist"),
            "GET    /api/v1/entity/99/data" => Permission::for("get", "/api/v1/entity/99/data", "This entity does not exist"),
            "POST   /api/v1/entity" => Permission::for("post", "/api/v1/entity", "This type is not an allowed sub-type.", [
                'name' => 'Test Entity',
                'entity_type_id' => 3,
                'root_entity_id' => 2,
            ]),
            "POST   /api/v1/entity" => Permission::for("post", "/api/v1/entity", "This type is not an allowed root-type.", [
                'name' => 'Test Entity',
                'entity_type_id' => 4,
            ]),
            "PATCH  /api/v1/entity/99/attributes" => Permission::for("patch", "/api/v1/entity/99/attributes", "This entity does not exist"),
            "PATCH  /api/v1/entity/99/attribute/13" => Permission::for("patch", "/api/v1/entity/99/attribute/13", "This entity does not exist"),
            "PATCH  /api/v1/entity/1/attribute/99" => Permission::for("patch", "/api/v1/entity/1/attribute/99", "This attribute does not exist"),
            "PATCH  /api/v1/entity/99/name" => Permission::for("patch", "/api/v1/entity/99/name", "This entity does not exist", [
                'name' => 'Test'
            ]),
            "PATCH  /api/v1/entity/99/rank" => Permission::for("patch", "/api/v1/entity/99/rank", "This entity does not exist", [
                'rank' => 1,
            ]),
        ];
    }
}
