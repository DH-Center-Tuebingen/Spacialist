<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

use App\AttributeValue;
use App\Entity;

class ApiEntityTest extends TestCase
{
    // Testing GET requests

    /**
     * Test getting all top entities.
     *
     * @return void
     */
    public function testTopEntityEndpoint()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/entity/top');

        $response->assertJsonCount(3);
        $response->assertJsonStructure([
            [
                'id',
                'name',
                'entity_type_id',
                'root_entity_id',
                'geodata_id',
                'rank',
                'lasteditor',
                'created_at',
                'updated_at',
                'parentIds',
                'parentNames'
            ]
        ]);
    }

    /**
     * Test getting entity (id=1).
     *
     * @return void
     */
    public function testEntityEndpoint()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/entity/1');

        $response->assertExactJson([
            'id' => 1,
            'name' => 'Site A',
            'entity_type_id' => 3,
            'root_entity_id' => null,
            'geodata_id' => 2,
            'rank' => 1,
            'lasteditor' => 'Admin',
            'created_at' => '2017-12-20 17:10:34',
            'updated_at' => '2017-12-31 16:10:56',
            'parentIds' => [1],
            'parentNames' => ['Site A']
        ]);
    }

    /**
     * Test getting entity (id=2).
     *
     * @return void
     */
    public function testEntityEndpointId()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/entity/2');

        $response->assertJson([
            'id' => 2,
        ]);
    }

    /**
     * Test getting non-existing entity.
     *
     * @return void
     */
    public function testEntityWrongIdEndpoint()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/entity/-1');

        $response->assertStatus(404);
    }

    /**
     * Test getting attribute values (id=15) of an entity-type (id=3).
     *
     * @return void
     */
    public function testEntityTypeDataEndpoint()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/entity/entity_type/3/data/15');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'entity_id',
                'attribute_id',
                'value',
                'lasteditor',
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
     * Test getting data of an entity (id=1).
     *
     * @return void
     */
    public function testEntityDataEndpoint()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/entity/1/data');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonStructure([
            15 => [
                'id',
                'entity_id',
                'attribute_id',
                'value',
                'lasteditor',
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
     * Test getting data of an non-existing entity.
     *
     * @return void
     */
    public function testEntityDataWithWrongIdEndpoint()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/entity/99/data');

        $response->assertStatus(400);
        $response->assertExactJson([
            'error' => 'This entity does not exist'
        ]);
    }

    /**
     * Test getting data of an attribute (id=15) of an entity (id=1).
     *
     * @return void
     */
    public function testEntityDataWithAttributeEndpoint()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/entity/1/data/15');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonStructure([
            15 => [
                'id',
                'entity_id',
                'attribute_id',
                'value',
                'lasteditor',
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
     * Test getting data with wrong attribute ID.
     *
     * @return void
     */
    public function testEntityDataWithWrongAttributeEndpoint()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/entity/1/data/99');

        $response->assertStatus(400);
        $response->assertExactJson([
            'error' => 'This attribute does not exist'
        ]);
    }

    /**
     * Test getting IDs of all parents (and own id) of an entity (id=5).
     *
     * @return void
     */
    public function testEntityParentIdEndpoint()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/entity/5/parentIds');

        $response->assertJsonCount(3);
        $response->assertExactJson([
            1, 2, 5
        ]);
    }

    /**
     * Test getting all sub-entities/children of an entity (id=2).
     *
     * @return void
     */
    public function testEntityParentEndpoint()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/entity/byParent/2');

        $response->assertJsonCount(3);
        $response->assertJsonStructure([
            [
                'id',
                'name',
                'entity_type_id',
                'root_entity_id',
                'geodata_id',
                'rank',
                'lasteditor',
                'created_at',
                'updated_at',
                'parentIds'
            ]
        ]);
        $response->assertJson([
            [
                'id' => 3
            ],
            [
                'id' => 4
            ],
            [
                'id' => 5
            ],
        ]);
    }

    // Testing POST requets

    /**
    * Test adding a new entity.
    *
    * @return void
    */
    public function testNewEntityEndpoint()
    {
        $cnt = Entity::count();
        $this->assertEquals($cnt, 8);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token"
        ])
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
            'geodata_id',
            'rank',
            'lasteditor',
            'created_at',
            'updated_at',
            'parentIds'
        ]);
        $response->assertJson([
            'name' => 'Unit-Test Entity I',
            'entity_type_id' => 3,
            'root_entity_id' => 1
        ]);

        $cnt = Entity::count();
        $this->assertEquals($cnt, 9);
    }

    // Testing PATCH requets

    /**
    * Test modifying (add, replace, remove) attributes of an entity (id=4).
    *
    * @return void
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

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token"
        ])
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
        $response->assertJsonStructure([
            'id',
            'name',
            'entity_type_id',
            'root_entity_id',
            'geodata_id',
            'rank',
            'lasteditor',
            'created_at',
            'updated_at',
            'parentIds'
        ]);

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
    * Test changing certainty of an attribute (id=5) of an entity (id=8).
    *
    * @return void
    */
    public function testPatchAttributeEndpoint()
    {
        $attrValue = AttributeValue::where('entity_id', 8)
            ->where('attribute_id', 5)
            ->first();
        $this->assertEquals(100, $attrValue->certainty);
        $this->assertNull($attrValue->certainty_description);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token"
        ])
        ->patch('/api/v1/entity/8/attribute/5', [
            'certainty' => 37,
            'certainty_description' => 'This is a test'
        ]);

        $response->assertStatus(204);

        $attrValue = AttributeValue::where('entity_id', 8)
            ->where('attribute_id', 5)
            ->first();
        $this->assertEquals(37, $attrValue->certainty);
        $this->assertEquals('This is a test', $attrValue->certainty_description);
    }

    /**
    * Test renaming an entity from 'Site A' to 'Site A_renamed'.
    *
    * @return void
    */
    public function testPatchRenameEntityEndpoint()
    {
        $entity = Entity::find(1);
        $this->assertEquals('Site A', $entity->name);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token"
        ])
        ->patch('/api/v1/entity/1/name', [
            'name' => 'Site A_renamed'
        ]);

        $response->assertStatus(204);

        $entity = Entity::find(1);
        $this->assertEquals('Site A_renamed', $entity->name);
    }

    /**
    * Test moving an entity (id=1) to another entity (id=7).
    *
    * @return void
    */
    public function testPatchMoveEntityEndpoint()
    {
        $entity = Entity::find(1);
        $this->assertEquals('Site A', $entity->name);
        $this->assertNull($entity->root_entity_id);
        $anotherEntity = Entity::find(7);
        $this->assertEquals('Site B', $anotherEntity->name);
        $this->assertNull($anotherEntity->root_entity_id);
        $this->assertEquals(2, $anotherEntity->rank);
        $YetAnotherEntity = Entity::find(8);
        $this->assertEquals('Fund 12', $YetAnotherEntity->name);
        $this->assertEquals(1, $YetAnotherEntity->rank);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token"
        ])
        ->patch('/api/v1/entity/1/rank', [
            'rank' => 1,
            'parent_id' => 7
        ]);

        $response->assertStatus(204);

        $entity = Entity::find(1);
        $anotherEntity = Entity::find(7);
        $YetAnotherEntity = Entity::find(8);
        $this->assertEquals(7, $entity->root_entity_id);
        $this->assertEquals(7, $YetAnotherEntity->root_entity_id);
        $this->assertEquals(1, $entity->rank);
        $this->assertEquals(1, $anotherEntity->rank);
        $this->assertEquals(2, $YetAnotherEntity->rank);
    }

    // Testing DELETE requets

    /**
    * Test deleting an entity (id=1) and all it's sub-entities.
    *
    * @return void
    */
    public function testDeleteEntityEndpoint()
    {
        $cnt = Entity::count();
        $this->assertEquals($cnt, 8);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token"
        ])
        ->delete('/api/v1/entity/1');

        $response->assertStatus(204);

        $cnt = Entity::count();
        $this->assertEquals($cnt, 3);
    }

    /**
    * Test deleting an entity (id=8) with no sub-entities.
    *
    * @return void
    */
    public function testDeleteEntityEightEndpoint()
    {
        $cnt = Entity::count();
        $this->assertEquals($cnt, 8);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token"
        ])
        ->delete('/api/v1/entity/8');

        $response->assertStatus(204);

        $cnt = Entity::count();
        $this->assertEquals($cnt, 7);
    }

    /**
    * A basic test example.
    *
    * @return void
    */
    public function testDeleteEntityWrongIdEndpoint()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token"
        ])
        ->delete('/api/v1/entity/99');

        $response->assertStatus(400);
        $response->assertExactJson([
            'error' => 'This entity does not exist'
        ]);
    }
}
