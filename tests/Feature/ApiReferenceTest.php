<?php

namespace Tests\Feature;

use App\Reference;
use Tests\Permission;
use Tests\PermissionTester;
use Tests\TestCase;

class ApiReferenceTest extends TestCase
{
    
    // ==========================================
    //                [[ GET ]]
    // ==========================================
    
    /**
    * @testdox GET    /api/v1/entity/{id}/reference  -  Ger all references of an entity (id=1).
    */
    public function testEntityReferencesEndpoint()
    {
        $response = $this->userRequest()
            ->get('/api/v1/entity/1/reference');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
        $response->assertJsonStructure([
            '*' => [
                [
                    'id',
                    'entity_id',
                    'attribute_id',
                    'bibliography_id',
                    'description',
                    'user_id',
                    'created_at',
                    'updated_at',
                    'bibliography'
                ]
            ]
        ]);
        $response->assertJson([
            'https://spacialist.escience.uni-tuebingen.de/<user-project>/alternativer_name#20171220165047' => [
                [
                    'id' => 1,
                    'entity_id' => 1,
                    'attribute_id' => 15,
                    'bibliography_id' => 1318,
                    'description' => 'See Page 10',
                    'user_id' => 1,
                    'created_at' => '2019-03-08T13:36:36.000000Z',
                    'updated_at' => '2019-03-08T13:36:36.000000Z',
                    'bibliography' => [
                        'id' => 1318
                    ],
                ],
                [
                    'id' => 2,
                    'entity_id' => 1,
                    'attribute_id' => 15,
                    'bibliography_id' => 1319,
                    'description' => 'Picture on left side of page 12',
                    'user_id' => 1,
                    'created_at' => '2019-03-08T13:36:48.000000Z',
                    'updated_at' => '2019-03-08T13:36:48.000000Z',
                    'bibliography' => [
                        'id' => 1319
                    ],
                ],
            ],
            'https://spacialist.escience.uni-tuebingen.de/<user-project>/notizen#20171220105603' => [
                [
                    'id' => 3,
                    'entity_id' => 1,
                    'attribute_id' => 13,
                    'bibliography_id' => 1323,
                    'description' => 'Page 10ff is interesting',
                    'user_id' => 1,
                    'created_at' => '2019-03-08T13:37:09.000000Z',
                    'updated_at' => '2019-03-08T13:37:09.000000Z',
                    'bibliography' => [
                        'id' => 1323
                    ],
                ],
            ]
        ]);
    }
    
    // ==========================================
    //              [[ POST ]]
    // ==========================================
    
    /**
    * @testdox POST   /api/v1/entity/{entity_id}/reference/{attribute_id}  -  Add a new reference to an entity (id=2).
    */
    public function testNewReferenceEndpoint()
    {
        $cnt = Reference::count();
        $this->assertEquals($cnt, 3);

        $response = $this->userRequest()
        ->post('/api/v1/entity/2/reference/14', [
            'bibliography_id' => 1322,
            'description' => 'This is a simple test',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id',
            'entity_id',
            'attribute_id',
            'bibliography_id',
            'description',
            'user_id',
            'created_at',
            'updated_at',
            'bibliography',
        ]);
        $response->assertJson([
            'entity_id' => 2,
            'attribute_id' => 14,
            'bibliography_id' => 1322,
            'description' => 'This is a simple test',
            'user_id' => 1,
            'bibliography' => [
                'id' => 1322,
                'entry_type' => 'article',
                'citekey' => 'Sh:5'
            ],
        ]);

        $cnt = Reference::count();
        $this->assertEquals($cnt, 4);
    }
    
    
    // ==========================================
    //              [[ PATCH ]]
    // ==========================================
    
/**
     * @testdox PATCH  /api/v1/entity/{entity_id}/rank  -  Move an entity (id=1) to top.
     */
    public function testPatchReferenceEndpoint()
    {
        $reference = Reference::find(2);
        $this->assertEquals(1, $reference->entity_id);
        $this->assertEquals(15, $reference->attribute_id);
        $this->assertEquals(1319, $reference->bibliography_id);
        $this->assertEquals('Picture on left side of page 12', $reference->description);

        $response = $this->userRequest()
        ->patch('/api/v1/entity/reference/2', [
            'description' => 'Page 12 was wrong, it is Page 15!',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'entity_id',
            'attribute_id',
            'bibliography_id',
            'description',
            'user_id',
            'created_at',
            'updated_at',
        ]);
        $response->assertJson([
            'entity_id' => 1,
            'attribute_id' => 15,
            'bibliography_id' => 1319,
            'description' => 'Page 12 was wrong, it is Page 15!',
            'user_id' => 1,
        ]);
    }
    
    // ==========================================
    //              [[ DELETE ]]
    // ==========================================
    
    /**
     * @testdox DELETE /api/v1/entity/reference/{id}  -  Delete a reference (id=1).
     */
     public function testDeleteReferenceEndpoint()
    {
        $cnt = Reference::count();
        $this->assertEquals($cnt, 3);

        $response = $this->userRequest()
            ->delete('/api/v1/entity/reference/1');

        $response->assertStatus(204);

        $cnt = Reference::count();
        $this->assertEquals($cnt, 2);
    }
    
    // ==========================================
    //      [[ ADDITIONAL DATA PROVIDERS ]]
    // ==========================================
    
    /**
     * @dataProvider permissions
     * @testdox [[PROVIDER]] Routes Without Permissions
     */
    public function testWithoutPermission($permission){          
        (new PermissionTester($this))->testMissingPermission($permission);
    }
    /**
     * @dataProvider exceptions
     * @testdox [[PROVIDER]] Exceptions With Permissions
     */
    public function testSucceedWithPermission($permission){
        (new PermissionTester($this))->testExceptions($permission);
    }
    
    public static function permissions(){
        return [
            "GET    /api/v1/entity/99/reference"    => Permission::for("get",      "/api/v1/entity/99/reference",      "You do not have the permission to view references"),
            "POST   /api/v1/entity/99/reference/99" => Permission::for("post",     "/api/v1/entity/99/reference/99",   "You do not have the permission to add references"),
            "PATCH  /api/v1/entity/reference/99"    => Permission::for("patch",    "/api/v1/entity/reference/99",      "You do not have the permission to edit references"),
            "DELETE /api/v1/entity/reference/99"    => Permission::for("delete",   "/api/v1/entity/reference/99",      "You do not have the permission to delete references"),
        ];
    }
    
    public static function exceptions(){
        return [
            "GET    /api/v1/entity/99/reference" =>Permission::for("get",      "/api/v1/entity/99/reference",      "This entity does not exist"),
            "POST   /api/v1/entity/99/reference/99" =>Permission::for("post",     "/api/v1/entity/99/reference/99",   "This entity does not exist", ["bibliography_id" => 1322, "description" => "This is a simple test"]),
            "POST   /api/v1/entity/1/reference/99" =>Permission::for("post",     "/api/v1/entity/1/reference/99",      "This attribute does not exist", ["bibliography_id" => 1322, "description" => "This is a simple test"]),
            "PATCH  /api/v1/entity/reference/99" =>Permission::for("patch",    "/api/v1/entity/reference/99",      "This reference does not exist"),
            "DELETE /api/v1/entity/reference/99" =>Permission::for("delete",   "/api/v1/entity/reference/99",      "This reference does not exist"),
        ];
    }
}