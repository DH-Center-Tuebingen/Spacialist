<?php

namespace Tests\Feature;

use Tests\TestCase;

use App\User;
use App\Role;

class ApiUserTest extends TestCase
{
    // Testing GET requests

    /**
     * Test getting the authenticated user.
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
     * Test getting all users.
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
     * Test getting all roles.
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

    /**
     * Test refreshing JWT token.
     *
     * @return void
     */
    public function testRefreshTokenEndpoint()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/auth/refresh');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertExactJson([
            'status' => 'success'
        ]);
    }

    // Testing POST requests

    /**
     * Test login.
     *
     * @return void
     */
    public function testLoginEndpoint()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->post('/api/v1/auth/login', [
                'email' => 'admin@admin.com',
                'password' => 'admin'
            ]);

        $response->assertStatus(200);
        $this->assertTrue($response->headers->has('authorization'));
    }

    /**
     * Test creating a new user.
     *
     * @return void
     */
    public function testCreateUserEndpoint()
    {
        $cnt = User::count();
        $this->assertEquals(1, $cnt);
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->post('/api/v1/user', [
                'email' => 'test@test.com',
                'name' => 'Test User',
                'password' => 'test'
            ]);

        $user = User::latest()->first();
        $cnt = User::count();
        $this->assertEquals(2, $cnt);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'email',
            'name',
            'created_at',
            'updated_at'
        ]);
        $response->assertJson([
            'id' => $user->id,
            'email' => 'test@test.com',
            'name' => 'Test User',
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at
        ]);
    }

    /**
     * Test creating a new role.
     *
     * @return void
     */
    public function testCreateRoleEndpoint()
    {
        $cnt = Role::count();
        $this->assertEquals(2, $cnt);
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->post('/api/v1/role', [
                'name' => 'test_role',
                'display_name' => 'Test Role'
            ]);

        $role = Role::latest()->first();
        $cnt = Role::count();
        $this->assertEquals(3, $cnt);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'name',
            'display_name',
            'description',
            'created_at',
            'updated_at'
        ]);
        $response->assertJson([
            'id' => $role->id,
            'name' => 'test_role',
            'display_name' => 'Test Role',
            'description' => null,
            'created_at' => $role->created_at,
            'updated_at' => $role->updated_at
        ]);
    }

    /**
     * Test user logout.
     *
     * @return void
     */
    public function testLogoutEndpoint()
    {
        $cnt = Role::count();
        $this->assertEquals(2, $cnt);
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->post('/api/v1/auth/logout');

        $response->assertStatus(200);
        if($response->headers->has('authorization')) {
            $token = $response->headers->get('authorization');

            $response = $this->withHeaders([
                'Authorization' => "$token"
            ])
            ->get('/api/v1/user');
            $response->assertStatus(302);
            $this->assertEquals("Unauthenticated.", $response->exception->getMessage());
        }
    }

    /**
     * Test patching user.
     *
     * @return void
     */
    public function testPatchUserEndpoint()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->patch('/api/v1/user/1', [
                'roles' => [2],
                'email' => 'test@test.com'
            ]);

        $user = User::find(1);
        $this->assertTrue(!$user->hasRole('admin'));
        $this->assertTrue($user->hasRole('guest'));
        $response->assertStatus(200);
        $response->assertExactJson([
            'id' => 1,
            'name' => 'Admin',
            'email' => 'test@test.com',
            'created_at' => '2017-12-20 09:47:36',
            'updated_at' => "$user->updated_at"
        ]);
    }

    /**
     * Test patching role.
     *
     * @return void
     */
    public function testPatchRoleEndpoint()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->patch('/api/v1/role/1', [
                'permissions' => [1, 2],
                'display_name' => 'NOT Admin',
                'description' => 'No longer a Admin User'
            ]);

        $role = Role::find(1);
        $this->assertTrue($role->hasPermission('create_concepts'));
        $this->assertTrue($role->hasPermission('delete_move_concepts'));
        $this->assertTrue(!$role->hasPermission('duplicate_edit_concepts'));
        $this->assertEquals(2, count($role->permissions));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'name',
            'display_name',
            'description',
            'created_at',
            'updated_at',
            'permissions'
        ]);
        $response->assertJson([
            'id' => 1,
            'name' => 'admin',
            'display_name' => 'NOT Admin',
            'description' => 'No longer a Admin User',
            'created_at' => '2017-12-20 09:47:35',
            'updated_at' => "$role->updated_at"
        ]);
    }

    /**
     * Test deleting user (id=1).
     *
     * @return void
     */
    public function testDeleteUserEndpoint()
    {
        $cnt = User::count();
        $this->assertEquals(1, $cnt);
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->delete('/api/v1/user/1');

        $cnt = User::count();
        $this->assertEquals(0, $cnt);

        $response->assertStatus(204);
    }

    /**
     * Test deleting non-existing user.
     *
     * @return void
     */
    public function testDeleteNonExstingUserEndpoint()
    {
        $cnt = User::count();
        $this->assertEquals(1, $cnt);
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->delete('/api/v1/user/99');

        $cnt = User::count();
        $this->assertEquals(1, $cnt);

        $response->assertStatus(400);
        $response->assertExactJson([
            'error' => 'This user does not exist'
        ]);
    }

    /**
     * Test deleting role (id=1).
     *
     * @return void
     */
    public function testDeleteRoleEndpoint()
    {
        $cnt = Role::count();
        $this->assertEquals(2, $cnt);
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->delete('/api/v1/role/1');

        $cnt = Role::count();
        $this->assertEquals(1, $cnt);

        $response->assertStatus(204);
    }

    /**
     * Test deleting non-existing role.
     *
     * @return void
     */
    public function testDeleteNonExstingRoleEndpoint()
    {
        $cnt = Role::count();
        $this->assertEquals(2, $cnt);
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->delete('/api/v1/role/99');

        $cnt = Role::count();
        $this->assertEquals(2, $cnt);

        $response->assertStatus(400);
        $response->assertExactJson([
            'error' => 'This role does not exist'
        ]);
    }
}
