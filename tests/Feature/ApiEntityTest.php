<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;

use App\Entity;

class ApiEntityTest extends TestCase
{
    // Testing GET requests

    /**
     * A basic test example.
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
                'parentIds'
            ]
        ]);
    }

    /**
     * A basic test example.
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
            'parentIds' => [1]
        ]);
    }

    /**
     * A basic test example.
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
     * A basic test example.
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
     * A basic test example.
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
     * A basic test example.
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
     * A basic test example.
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
     * A basic test example.
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
     * A basic test example.
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
     * A basic test example.
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
     * A basic test example.
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

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testEntityChildrenEndpoint()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/entity/2/children');

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
    * A basic test example.
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

    // Testing DELETE requets

    /**
    * A basic test example.
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
    * A basic test example.
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
