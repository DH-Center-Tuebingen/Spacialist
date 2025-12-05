<?php

use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\TestDox;

use App\User;
use App\Role;
use Carbon\Carbon;


test('get user endpoint', function () {
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
    expect(45)->toEqual(count(get_object_vars($content->data->permissions)));
});

test('get users endpoint', function () {
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
});

test('get roles endpoint', function () {
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
});

test('login endpoint', function () {
    $response = $this->userRequest()
        ->post('/api/v1/auth/login', [
            'email' => 'admin@localhost',
            'password' => 'admin'
        ]);

    $response->assertStatus(200);
});

test('login with nickname endpoint', function () {
    $response = $this->userRequest()
        ->post('/api/v1/auth/login', [
            'nickname' => 'admin',
            'password' => 'admin'
        ]);

    $response->assertStatus(200);
});

test('login wrong credentials endpoint', function () {
    $response = $this->userRequest()
        ->post('/api/v1/auth/login', [
            'email' => 'admin@localhost',
            'password' => 'admin1337'
        ]);

    $response->assertStatus(400);
    $response->assertSimilarJson([
        'error' => 'Invalid Credentials'
    ]);
});

test('create user endpoint', function () {
    $cnt = User::count();
    expect($cnt)->toEqual(2);
    $cnt = User::withTrashed()->count();
    expect($cnt)->toEqual(3);
    $response = $this->userRequest()
        ->post('/api/v1/user', [
            'email' => 'test@test.com',
            'name' => 'Test User',
            'nickname' => 'tuser',
            'password' => 'test1234' // at least 6 characters
        ]);

    $user = User::latest()->first();
    $cnt = User::count();
    expect($cnt)->toEqual(3);
    $cnt = User::withTrashed()->count();
    expect($cnt)->toEqual(4);
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
});

test('create avatar endpoint', function () {
    $user = User::find(1);
    expect($user->avatar)->toBeNull();
    $file = UploadedFile::fake()->image('spacialist_screenshot.png', 350, 100);
    $response = $this->userRequest()
        ->post('/api/v1/user/avatar', [
            'file' => $file
        ]);

    $user = User::find(1);
    expect($user->avatar)->toEqual('avatars/1.png');
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
});

test('create role endpoint', function () {
    $cnt = Role::count();
    expect($cnt)->toEqual(2);
    $response = $this->userRequest()
        ->post('/api/v1/role', [
            'name' => 'test_role',
            'display_name' => 'Test Role'
        ]);

    $role = Role::latest()->first();
    $cnt = Role::count();
    expect($cnt)->toEqual(3);
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
});

test('logout endpoint', function () {
    $cnt = Role::count();
    expect($cnt)->toEqual(2);
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
        expect($response->exception->getMessage())->toEqual("Unauthenticated.");
    }
});

test('update avatar endpoint', function () {
    $user = User::find(1);
    expect($user->avatar)->toBeNull();
    $file = UploadedFile::fake()->image('spacialist_screenshot.png', 350, 100);
    $response = $this->userRequest()
        ->post('/api/v1/user/avatar', [
            'file' => $file
        ]);

    $user = User::find(1);
    expect($user->avatar)->toEqual('avatars/1.png');
    $this->assertStatus($response, 200);
});

test('patch user endpoint', function () {
    $user = User::find(1);
    expect($user->name)->toEqual('Admin');
    expect($user->nickname)->toEqual('admin');
    expect($user->email)->toEqual('admin@localhost');
    expect($user->metadata)->toBeNull();
    expect($user->avatar)->toBeNull();

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
    expect(!$user->hasRole('admin'))->toBeTrue();
    expect($user->hasRole('guest'))->toBeTrue();
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

    expect($user->name)->toEqual('Admin Updated');
    expect($user->nickname)->toEqual('admin1');
    expect($user->email)->toEqual('test@test.com');
    expect($user->metadata['phonenumber'])->toEqual('+43 123 1234');
    expect($user->metadata['orcid'])->toEqual('0000-0002-1694-233X');
    expect($user->avatar)->toBeNull();
});

test('patch user orcid endpoint', function () {
    $user = User::find(1);
    expect($user->metadata)->toBeNull();
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
    expect($user->metadata)->not->toBeNull();
    expect($user->metadata['orcid'])->toEqual('0000-0001-5109-3700');
});

test('patch user wrong orcid endpoint', function () {
    $user = User::find(1);

    $response = $this->userRequest()
        ->json('patch', '/api/v1/user/1', [
            'orcid' => '0000-0002-1694-2338'
        ]);

    $response->assertStatus(422);
});

test('patch user another wrong orcid endpoint', function () {
    $user = User::find(1);

    $response = $this->userRequest()
        ->json('patch', '/api/v1/user/1', [
            'orcid' => '0000-0002-1694233X'
        ]);

    $response->assertStatus(422);
});

test('patch user another second wrong orcid endpoint', function () {
    $user = User::find(1);

    $response = $this->userRequest()
        ->json('patch', '/api/v1/user/1', [
            'orcid' => '0000-0002-1694-23aX'
        ]);

    $response->assertStatus(422);
});

test('patch user without data endpoint', function () {
    $response = $this->userRequest()
        ->patch('/api/v1/user/1', []);

    $response->assertStatus(204);
});

test('patch role endpoint', function () {
    $response = $this->userRequest()
        ->patch('/api/v1/role/1', [
            'permissions' => [1, 2],
            'display_name' => 'NOT Admin',
            'description' => 'No longer a Admin User'
        ]);

    $role = Role::find(1);
    expect($role->hasPermissionTo('entity_read'))->toBeTrue();
    expect($role->hasPermissionTo('entity_write'))->toBeTrue();
    expect(!$role->hasPermissionTo('entity_create'))->toBeTrue();
    expect(count($role->permissions))->toEqual(2);
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
});

test('patch role without data endpoint', function () {
    $response = $this->userRequest()
        ->patch('/api/v1/role/1', []);

    $response->assertStatus(204);
});

test('restore user endpoint', function () {
    $user = User::find(1);
    $user->deleted_at = Carbon::now();
    $user->save();

    $cnt = User::withTrashed()->count();
    expect($cnt)->toEqual(3);
    $cnt = User::onlyTrashed()->count();
    expect($cnt)->toEqual(2);
    $cnt = User::withoutTrashed()->count();
    expect($cnt)->toEqual(1);
    $user = User::onlyTrashed()->find(1);
    expect($user->deleted_at)->not->toBeNull();

    $response = $this->userRequest()
        ->patch('/api/v1/user/restore/1');

    $user = User::find(2);
    expect($user->deleted_at)->toBeNull();

    $response->assertStatus(204);
});

test('delete user endpoint', function () {
    $cnt = User::withTrashed()->count();
    expect($cnt)->toEqual(3);
    $cnt = User::onlyTrashed()->count();
    expect($cnt)->toEqual(1);
    $cnt = User::withoutTrashed()->count();
    expect($cnt)->toEqual(2);
    $response = $this->userRequest()
        ->delete('/api/v1/user/1');

    $response->assertStatus(200);

    $cnt = User::withTrashed()->count();
    expect($cnt)->toEqual(3);
    $cnt = User::onlyTrashed()->count();
    expect($cnt)->toEqual(2);
    $cnt = User::withoutTrashed()->count();
    expect($cnt)->toEqual(1);
    $user = User::onlyTrashed()->find(1);
    expect($user->deleted_at)->not->toBeNull();

    $response = $this->userRequest()
        ->patch('/api/v1/user/restore/1');

    $user = User::find(1);
    expect($user->deleted_at)->toBeNull();

    $response->assertStatus(204);
});

test('delete non exsting user endpoint', function () {
    $cnt = User::withTrashed()->count();
    expect($cnt)->toEqual(3);
    $cnt = User::count();
    expect($cnt)->toEqual(2);
    $response = $this->userRequest()
        ->delete('/api/v1/user/99');

    $cnt = User::withTrashed()->count();
    expect($cnt)->toEqual(3);
    $cnt = User::count();
    expect($cnt)->toEqual(2);

    $response->assertStatus(400);
    $response->assertSimilarJson([
        'error' => 'This user does not exist'
    ]);
});

test('delete role endpoint', function () {
    $cnt = Role::count();
    expect($cnt)->toEqual(2);
    $response = $this->userRequest()
        ->delete('/api/v1/role/1');

    $cnt = Role::count();
    expect($cnt)->toEqual(1);

    $response->assertStatus(204);
});

test('delete non exsting role endpoint', function () {
    $cnt = Role::count();
    expect($cnt)->toEqual(2);
    $response = $this->userRequest()
        ->delete('/api/v1/role/99');

    $cnt = Role::count();
    expect($cnt)->toEqual(2);

    $response->assertStatus(400);
    $response->assertSimilarJson([
        'error' => 'This role does not exist'
    ]);
});

test('delete avatar endpoint', function () {
    $response = $this->userRequest()
        ->delete('/api/v1/user/avatar');

    $user = User::find(1);
    expect($user->avatar)->toBeNull();

    $this->assertStatus($response, 204);
});

test('permissions', function () {
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
});

test('exceptions', function () {
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
});

test('validations', function () {
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

    expect($response->exception->getMessage())->toEqual('The email has already been taken.');

    $response = $this->userRequest()
        ->patch('/api/v1/user/' . $user->id, [
            'email' => 'admin@localhost!'
        ]);

    expect($response->exception->getMessage())->toEqual('The email must be a valid email address.');
});
