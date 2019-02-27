<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

use App\User;

class ApiUserTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGetUserEndpoint()
    {
        $user = User::find(1);
        $user->setPermissions();
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/auth/user');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
        $response->assertJsonStructure([
            'status',
            'data'
        ]);
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'id' => 1,
                'name' => 'Admin',
                'email' => 'admin@admin.com',
                'created_at' => '2017-12-20 09:47:36',
                'updated_at' => '2017-12-20 09:47:36',
                'permissions' => []
            ]
        ]);

        // Check permission count (30 permissions in total)
        $content = json_decode($response->getContent());
        $this->assertEquals(count(get_object_vars($content->data->permissions)), 30);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGetUsersEndpoint()
    {
        $user = factory(User::class)->create();

        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/user');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
        $response->assertJsonStructure([
            'users',
            'roles'
        ]);
        $response->assertJson([
            'users' => [
                [
                    'id' => 1
                ],
                [
                    'id' => $user->id
                ]
            ],
            'roles' => []
        ]);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGetRolesEndpoint()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/role');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
        $response->assertJsonStructure([
            'roles',
            'permissions'
        ]);
        $response->assertJson([
            'roles' => [
                [
                    'id' => 1,
                    'name' => 'admin'
                ],
                [
                    'id' => 2,
                    'name' => 'guest'
                ]
            ],
            'permissions' => []
        ]);
    }
}
