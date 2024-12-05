<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;

use App\User;
use App\Role;
use Carbon\Carbon;

class ApiUserTest extends TestCase
{
    // Testing GET requests

    /**
     * @testdox GET    /api/v1/auth/user : Get Auth User
     *
     * @return void
     */
    public function testGetUserEndpoint()
    {
        $user = User::find(1);
        $user->setPermissions();
        $response = $this->userRequest()
            ->get('/api/v1/auth/user');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data'
        ]);
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'id' => 1,
                'name' => 'Admin',
                'nickname' => 'admin',
                'email' => 'admin@localhost',
                'created_at' => '2017-12-20T09:47:36.000000Z',
                'updated_at' => '2017-12-20T09:47:36.000000Z',
                'permissions' => []
            ]
        ]);

        // Check permission count (45 permissions in total)
        $content = json_decode($response->getContent());
        $this->assertEquals(count(get_object_vars($content->data->permissions)), 45);
    }

    /**
     * @testdox GET    /api/v1/user : Get All Users
     *
     * @return void
     */
    public function testGetUsersEndpoint()
    {
        $user = User::factory()->create();

        $response = $this->userRequest()
            ->get('/api/v1/user');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
        $response->assertJsonStructure([
            'users',
            'deleted_users',
        ]);
        $response->assertJson([
            'users' => [
                [
                    'id' => 1,
                    'name' => 'Admin',
                ],[
                  'id' => 2,
                    'name' => "John Doe",
                ],[
                    'id' => $user->id,
                    'name' => $user->name,
                ],
            ],
            'deleted_users' => [
                [
                    'id' => 3,
                    'name' => "Gary Guest",
                ],
            ]
        ]);
    }

    /**
     * @testdox GET    /api/v1/role : Get All Roles
     *
     * @return void
     */
    public function testGetRolesEndpoint()
    {
        $response = $this->userRequest()
            ->get('/api/v1/role');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
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
            'permissions' => [],
            'presets' => [],
        ]);
    }

    // Testing POST requests

    /**
     * @testdox GET    /api/v1/auth/login : Login
     *
     * @return void
     */
    public function testLoginEndpoint()
    {
        $response = $this->userRequest()
            ->post('/api/v1/auth/login', [
                'email' => 'admin@localhost',
                'password' => 'admin'
            ]);

        $response->assertStatus(200);
    }

    /**
     * @testdox GET    /api/v1/auth/login : Login with nickname
     *
     * @return void
     */
    public function testLoginWithNicknameEndpoint()
    {
        $response = $this->userRequest()
            ->post('/api/v1/auth/login', [
                'nickname' => 'admin',
                'password' => 'admin'
            ]);

        $response->assertStatus(200);
    }

    /**
     * @testdox GET    /api/v1/user : Failed Login
     *
     * @return void
     */
    public function testLoginWrongCredentialsEndpoint()
    {
        $response = $this->userRequest()
            ->post('/api/v1/auth/login', [
                'email' => 'admin@localhost',
                'password' => 'admin1337'
            ]);

        $response->assertStatus(400);
        $response->assertSimilarJson([
            'error' => 'Invalid Credentials'
        ]);
    }

    /**
     * @testdox POST   /api/v1/user : Create User
     *
     * @return void
     */
    public function testCreateUserEndpoint()
    {
        $cnt = User::count();
        $this->assertEquals(3, $cnt);
        $response = $this->userRequest()
            ->post('/api/v1/user', [
                'email' => 'test@test.com',
                'name' => 'Test User',
                'nickname' => 'tuser',
                'password' => 'test1234' // at least 6 characters
            ]);

        $user = User::latest()->first();
        $cnt = User::count();
        $this->assertEquals(4, $cnt);
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
            'nickname' => 'tuser',
            'created_at' => $user->created_at->toJSON(),
            'updated_at' => $user->updated_at->toJSON()
        ]);
    }

    /**
     * @testdox POST   /api/v1/user/avatar : Add Avatar
     *
     * @return void
     */
    public function testCreateAvatarEndpoint()
    {
        $user = User::find(1);
        $this->assertNull($user->avatar);
        $file = UploadedFile::fake()->image('spacialist_screenshot.png', 350, 100);
        $response = $this->userRequest()
            ->post('/api/v1/user/avatar', [
                'file' => $file
            ]);

        $user = User::find(1);
        $this->assertEquals('avatars/1.png', $user->avatar);
        $this->assertStatus($response, 200);
        $response->assertJsonStructure([
            'id',
            'email',
            'name',
            'nickname',
            'created_at',
            'updated_at',
            'avatar'
        ]);
        $response->assertJson([
            'id' => 1,
            'email' => 'admin@localhost',
            'name' => 'Admin',
            'avatar' => 'avatars/1.png'
        ]);
    }

    /**
     * @testdox POST   /api/v1/role : Create Role
     *
     * @return void
     */
    public function testCreateRoleEndpoint()
    {
        $cnt = Role::count();
        $this->assertEquals(2, $cnt);
        $response = $this->userRequest()
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
            'created_at' => $role->created_at->toJSON(),
            'updated_at' => $role->updated_at->toJSON()
        ]);
    }

    /**
     * @testdox POST   /api/v1/auth/logout : Logout
     *
     * @return void
     */
    public function testLogoutEndpoint()
    {
        $cnt = Role::count();
        $this->assertEquals(2, $cnt);
        $response = $this->userRequest()
            ->post('/api/v1/auth/logout');

        $response->assertStatus(200);
        if($response->headers->has('authorization')) {
            $token = $response->headers->get('authorization');

            $response = $this->withHeaders([
                'Authorization' => "$token"
            ])
            ->get('/api/v1/user');
            $response->assertStatus(401);
            $this->assertEquals("Unauthenticated.", $response->exception->getMessage());
        }
    }

    /**
     * @testdox POST   /api/v1/user/avatar : Update User Avatar
     *
     * @return void
     */
    public function testUpdateAvatarEndpoint()
    {
        $user = User::find(1);
        $this->assertNull($user->avatar);
        $file = UploadedFile::fake()->image('spacialist_screenshot.png', 350, 100);
        $response = $this->userRequest()
            ->post('/api/v1/user/avatar', [
                'file' => $file
            ]);

        $user = User::find(1);
        $this->assertEquals('avatars/1.png', $user->avatar);
        $this->assertStatus($response, 200);
    }

    /**
     * @testdox PATCH  /api/v1/user/{id} : Patch User
     *
     * @return void
     */
    public function testPatchUserEndpoint()
    {
        $user = User::find(1);
        $this->assertEquals('Admin', $user->name);
        $this->assertEquals('admin', $user->nickname);
        $this->assertEquals('admin@localhost', $user->email);
        $this->assertNull($user->metadata);
        $this->assertNull($user->avatar);

        $response = $this->userRequest()
            ->patch('/api/v1/user/1', [
                'roles' => [2],
                'email' => 'test@test.com',
                'name' => 'Admin Updated',
                'nickname' => 'admin1',
                'phonenumber' => '+43 123 1234',
                'orcid' => '0000-0002-1694-233X'
            ]);

        $user = User::find(1);
        $this->assertTrue(!$user->hasRole('admin'));
        $this->assertTrue($user->hasRole('guest'));
        $response->assertStatus(200);
        $response->assertSimilarJson([
            'id' => 1,
            'name' => 'Admin Updated',
            'nickname' => 'admin1',
            'email' => 'test@test.com',
            'created_at' => '2017-12-20T09:47:36.000000Z',
            'updated_at' => $user->updated_at->toJSON(),
            'deleted_at' => null,
            'avatar' => null,
            'login_attempts' => null,
            'metadata' => [
                'phonenumber' => '+43 123 1234',
                'orcid' => '0000-0002-1694-233X',
            ],
        ]);

        $this->assertEquals('Admin Updated', $user->name);
        $this->assertEquals('admin1', $user->nickname);
        $this->assertEquals('test@test.com', $user->email);
        $this->assertEquals('+43 123 1234', $user->metadata['phonenumber']);
        $this->assertEquals('0000-0002-1694-233X', $user->metadata['orcid']);
        $this->assertNull($user->avatar);
    }

    /**
     * @testdox PATCH  /api/v1/user/{id} : Patch User Metadata
     *
     * @return void
     */
    public function testPatchUserOrcidEndpoint()
    {
        $user = User::find(1);
        $this->assertNull($user->metadata);
        $user->metadata = [
            'orcid' => '0000-0002-1694-233X',
        ];
        $user->save();

        $response = $this->userRequest()
            ->patch('/api/v1/user/1', [
                'orcid' => '0000-0001-5109-3700'
            ]);

        $response->assertStatus(200);
        $user = User::find(1);
        $this->assertNotNull($user->metadata);
        $this->assertEquals('0000-0001-5109-3700', $user->metadata['orcid']);

    }

    /**
     * @testdox PATCH  /api/v1/user/{id} : Patch User Fail ORCID Check
     *
     * @return void
     */
    public function testPatchUserWrongOrcidEndpoint()
    {
        $user = User::find(1);

        $response = $this->userRequest()
            ->json('patch', '/api/v1/user/1', [
                'orcid' => '0000-0002-1694-2338'
            ]);

        $response->assertStatus(422);
    }

    /**
     * @testdox PATCH  /api/v1/user/{id} : Patch User Fail ORCID Format
     *
     * @return void
     */
    public function testPatchUserAnotherWrongOrcidEndpoint()
    {
        $user = User::find(1);

        $response = $this->userRequest()
            ->json('patch', '/api/v1/user/1', [
                'orcid' => '0000-0002-1694233X'
            ]);

        $response->assertStatus(422);
    }

    /**
     * @testdox PATCH  /api/v1/user/{id} : Patch User Fail ORCID Format
     *
     * @return void
     */
    public function testPatchUserAnotherSecondWrongOrcidEndpoint()
    {
        $user = User::find(1);

        $response = $this->userRequest()
            ->json('patch', '/api/v1/user/1', [
                'orcid' => '0000-0002-1694-23aX'
            ]);

        $response->assertStatus(422);
    }

    /**
     * @testdox PATCH  /api/v1/user/{id} : Patch User Emtpy Request
     *
     * @return void
     */
    public function testPatchUserWithoutDataEndpoint()
    {
        $response = $this->userRequest()
            ->patch('/api/v1/user/1', []);

        $response->assertStatus(204);
    }

    /**
     * @testdox PATCH  /api/v1/role/{id} : Patch Role Permissions
     *
     * @return void
     */
    public function testPatchRoleEndpoint()
    {
        $response = $this->userRequest()
            ->patch('/api/v1/role/1', [
                'permissions' => [1, 2],
                'display_name' => 'NOT Admin',
                'description' => 'No longer a Admin User'
            ]);

        $role = Role::find(1);
        $this->assertTrue($role->hasPermissionTo('entity_read'));
        $this->assertTrue($role->hasPermissionTo('entity_write'));
        $this->assertTrue(!$role->hasPermissionTo('entity_create'));
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
            'created_at' => '2017-12-20T09:47:35.000000Z',
            'updated_at' => $role->updated_at->toJSON()
        ]);
    }

    /**
     * @testdox PATCH  /api/v1/role/{id} : Patch Role Empty Request
     *
     * @return void
     */
    public function testPatchRoleWithoutDataEndpoint()
    {
        $response = $this->userRequest()
            ->patch('/api/v1/role/1', []);

        $response->assertStatus(204);
    }

    /**
     * @testdox PATCH  /api/v1/user/restore/{id} : Restore User
     *
     * @return void
     */
    public function testRestoreUserEndpoint()
    {
        $user = User::find(1);
        $user->deleted_at = Carbon::now();
        $user->save();

        $cnt = User::count();
        $this->assertEquals(3, $cnt);
        $cnt = User::onlyTrashed()->count();
        $this->assertEquals(2, $cnt);
        $cnt = User::withoutTrashed()->count();
        $this->assertEquals(1, $cnt);
        $user = User::find(1);
        $this->assertNotNull($user->deleted_at);

        $response = $this->userRequest()
            ->patch('/api/v1/user/restore/1');

        $user = User::find(2);
        $this->assertNull($user->deleted_at);

        $response->assertStatus(204);
    }

    /**
     * @testdox DELETE /api/v1/user/{id} : Delete User
     *
     * @return void
     */
    public function testDeleteUserEndpoint()
    {
        $cnt = User::count();
        $this->assertEquals(3, $cnt);
        $cnt = User::onlyTrashed()->count();
        $this->assertEquals(1, $cnt);
        $cnt = User::withoutTrashed()->count();
        $this->assertEquals(2, $cnt);
        $response = $this->userRequest()
            ->delete('/api/v1/user/1');

        $response->assertStatus(200);

        $cnt = User::count();
        $this->assertEquals(3, $cnt);
        $cnt = User::onlyTrashed()->count();
        $this->assertEquals(2, $cnt);
        $cnt = User::withoutTrashed()->count();
        $this->assertEquals(1, $cnt);
        $user = User::find(1);
        $this->assertNotNull($user->deleted_at);

        $response = $this->userRequest()
            ->patch('/api/v1/user/restore/1');

        $user = User::find(1);
        $this->assertNull($user->deleted_at);

        $response->assertStatus(204);
    }

    /**
     * @testdox DELETE /api/v1/user/99 : Delete User Fail
     *
     * @return void
     */
    public function testDeleteNonExstingUserEndpoint()
    {
        $cnt = User::count();
        $this->assertEquals(3, $cnt);
        $response = $this->userRequest()
            ->delete('/api/v1/user/99');

        $cnt = User::count();
        $this->assertEquals(3, $cnt);

        $response->assertStatus(400);
        $response->assertSimilarJson([
            'error' => 'This user does not exist'
        ]);
    }

    /**
     * @testdox DELETE /api/v1/role/{id} : Delete Role
     *
     * @return void
     */
    public function testDeleteRoleEndpoint()
    {
        $cnt = Role::count();
        $this->assertEquals(2, $cnt);
        $response = $this->userRequest()
            ->delete('/api/v1/role/1');

        $cnt = Role::count();
        $this->assertEquals(1, $cnt);

        $response->assertStatus(204);
    }

    /**
     * @testdox DELETE /api/v1/role/99 : Delete Role Fail
     *
     * @return void
     */
    public function testDeleteNonExstingRoleEndpoint()
    {
        $cnt = Role::count();
        $this->assertEquals(2, $cnt);
        $response = $this->userRequest()
            ->delete('/api/v1/role/99');

        $cnt = Role::count();
        $this->assertEquals(2, $cnt);

        $response->assertStatus(400);
        $response->assertSimilarJson([
            'error' => 'This role does not exist'
        ]);
    }

    /**
     * @testdox DELETE /api/v1/user/avatar : Delete User Avatar
     *
     * @return void
     */
    public function testDeleteAvatarEndpoint()
    {
        $response = $this->userRequest()
            ->delete('/api/v1/user/avatar');

        $user = User::find(1);
        $this->assertNull($user->avatar);

        $this->assertStatus($response, 204);
    }

    // Testing exceptions and permissions

    /**
     *
     *
     * @return void
     */
    public function testPermissions()
    {
        User::first()->roles()->detach();

        $calls = [
            ['url' => '/user', 'error' => 'You do not have the permission to view users', 'verb' => 'get'],
            ['url' => '/role', 'error' => 'You do not have the permission to view roles', 'verb' => 'get'],
            ['url' => '/user', 'error' => 'You do not have the permission to add new users', 'verb' => 'post'],
            ['url' => '/role', 'error' => 'You do not have the permission to add roles', 'verb' => 'post'],
            ['url' => '/user/1', 'error' => 'You do not have the permission to modify user data', 'verb' => 'patch'],
            ['url' => '/role/1', 'error' => 'You do not have the permission to set role permissions', 'verb' => 'patch'],
            ['url' => '/user/restore/1', 'error' => 'You do not have the permission to restore users', 'verb' => 'patch'],
            ['url' => '/user/1', 'error' => 'You do not have the permission to delete users', 'verb' => 'delete'],
            ['url' => '/role/1', 'error' => 'You do not have the permission to delete roles', 'verb' => 'delete'],
        ];

        $response = null;
        foreach($calls as $c) {
            $response = $this->userRequest()
                ->json($c['verb'], '/api/v1' . $c['url']);

            $this->assertStatus($response, 403);
            $response->assertSimilarJson([
                'error' => $c['error']
            ]);
        }
    }

    /**
     *
     *
     * @return void
     */
    public function testExceptions()
    {
        $calls = [
            ['url' => '/user/99', 'error' => 'This user does not exist', 'verb' => 'patch'],
            ['url' => '/role/99', 'error' => 'This role does not exist', 'verb' => 'patch'],
            ['url' => '/user/restore/99', 'error' => 'This user does not exist', 'verb' => 'patch'],
        ];

        foreach($calls as $c) {
            $response = $this->userRequest()
                ->json($c['verb'], '/api/v1' . $c['url'], [
                    'description' => 'does not matter'
                ]);

            $response->assertStatus(400);
            $response->assertSimilarJson([
                'error' => $c['error']
            ]);
        }
    }

    /**
     *
     *
     * @return void
     */
    public function testValidations()
    {
        $user = new User();
        $user->name = 'Test';
        $user->nickname = 'test';
        $user->email = 'mail@example.com';
        $user->password = 'not_safe';
        $user->save();

        $response = $this->userRequest()
            ->patch('/api/v1/user/' . $user->id, [
                'email' => 'admin@localhost'
            ]);

        $this->assertEquals('The email has already been taken.', $response->exception->getMessage());

        $response = $this->userRequest()
            ->patch('/api/v1/user/' . $user->id, [
                'email' => 'admin@localhost!'
            ]);

        $this->assertEquals('The email must be a valid email address.', $response->exception->getMessage());
    }
}
