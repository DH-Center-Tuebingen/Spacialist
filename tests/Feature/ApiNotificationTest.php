<?php

use App\Entity;
use App\Notifications\CommentPosted;
use App\User;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\TestDox;


function setupData()
{
    $testUser = new User();
    $testUser->name = 'Test User';
    $testUser->nickname = 'testuser';
    $testUser->email = 'test@localhost';
    $testUser->password = Hash::make('test');
    $testUser->save();
    $testUser->assignRole('admin');
    $testUser = User::find($testUser->id);

    $user = User::find(1);
    $entity = Entity::first();

    $entity->addComment([
        'content' => 'A simple test'
    ], $user, false, []);
    $entity->addComment([
        'content' => 'A simple test from a simple user'
    ], $testUser, true, []);
    $entity->load('comments');

    return [
        'users' => [$user, $testUser],
        'entity' => $entity,
    ];
}

test('resource comments', function () {
    $data = setupData();
    $user = $data['users'][0];
    $entity = $data['entity'];

    $eid = $entity->id;
    $response = $this->userRequest()
        ->get("/api/v1/comment/resource/$eid?r=entity");
    $response->assertStatus(200);
    $response->assertJsonCount(2);
    $response->assertJsonStructure([
        '*' => [
            'id',
            'user_id',
            'commentable_id',
            'commentable_type',
            'reply_to',
            'content',
            'metadata',
            'created_at',
            'updated_at',
            'deleted_at',
        ]
    ]);
});

test('comment replies', function () {
    $data = setupData();
    $user = $data['users'][0];
    $entity = $data['entity'];

    $eid = $entity->id;
    $cid = $entity->comments[1]->id;
    $response = $this->userRequest()
        ->get("/api/v1/comment/$cid/reply");
    $response->assertStatus(200);
    $response->assertJsonCount(0);

    $entity->addComment([
        'content' => 'A simple reply',
        'reply_to' => $cid,
    ], $user, false, []);

    $entity->load('comments');
    $entity->comments[1]->load('replies');
    expect(count($entity->comments))->toEqual(2);
    expect(count($entity->comments[1]->replies))->toEqual(1);
});

test('add comment', function () {
    $data = setupData();
    $user = $data['users'][0];
    $entity = $data['entity'];

    $response = $this->userRequest()
        ->post("/api/v1/comment", [
            'resource_type' => 'entity',
            'resource_id' => $entity->id,
            'content' => 'This is a test',
            'metadata' => [
                'key' => 'value'
            ]
        ]);
    $response->assertStatus(201);
    $response->assertJsonStructure([
        'id',
        'user_id',
        'commentable_id',
        'commentable_type',
        'reply_to',
        'content',
        'metadata',
        'created_at',
        'updated_at',
        'deleted_at',
        'author',
    ]);

    $entity->load('comments');
    expect(count($entity->comments))->toEqual(3);
    expect($entity->comments[2]->content)->toEqual('This is a test');
    expect($entity->comments[2]->metadata['key'])->toEqual('value');
});

test('update comment', function () {
    $data = setupData();
    $user = $data['users'][0];
    $testUser = $data['users'][1];
    $entity = $data['entity'];

    $entity->load('comments');
    $cid = $entity->comments[0]->id;

    $response = $this->userRequest()
        ->patch("/api/v1/comment/$cid", [
            'content' => 'This is still a test',
        ]);
    $this->assertStatus($response, 200);
    $response->assertJsonStructure([
        'id',
        'user_id',
        'commentable_id',
        'commentable_type',
        'reply_to',
        'content',
        'metadata',
        'created_at',
        'updated_at',
        'deleted_at',
    ]);

    $entity->load('comments');
    expect(count($entity->comments))->toEqual(2);
    expect($entity->comments[0]->content)->toEqual('This is still a test');
});

test('mark notification as read', function () {
    $data = setupData();
    $user = $data['users'][0];

    $user->load('notifications');
    $user->load('unreadNotifications');
    expect(count($user->notifications))->toEqual(1);
    expect(count($user->unreadNotifications))->toEqual(1);

    $id = $user->unreadNotifications[0]->id;

    $response = $this->userRequest()
        ->patch("/api/v1/notification/read/$id");

    $response->assertStatus(204);
    $user->load('notifications');
    $user->load('unreadNotifications');
    expect(count($user->notifications))->toEqual(1);
    expect(count($user->unreadNotifications))->toEqual(0);
});

test('mark notifications as read', function () {
    $data = setupData();
    $user = $data['users'][0];
    $entity = $data['entity'];

    $user->notify(new CommentPosted($entity->comments->last(), [], []));
    $user->load('notifications');
    $user->load('unreadNotifications');
    expect(count($user->notifications))->toEqual(2);
    expect(count($user->unreadNotifications))->toEqual(2);

    $ids = $user->unreadNotifications->pluck('id')->toArray();

    $response = $this->userRequest()
        ->patch("/api/v1/notification/read", [
            'ids' => $ids,
        ]);

    $response->assertStatus(204);
    $user->load('notifications');
    $user->load('unreadNotifications');
    expect(count($user->notifications))->toEqual(2);
    expect(count($user->unreadNotifications))->toEqual(0);
});

test('delete notifications', function () {
    $data = setupData();
    $user = $data['users'][0];
    $entity = $data['entity'];

    $user->notify(new CommentPosted($entity->comments->last(), [], []));
    $user->load('notifications');
    $user->load('unreadNotifications');
    expect(count($user->notifications))->toEqual(2);
    expect(count($user->unreadNotifications))->toEqual(2);

    $id = $user->unreadNotifications->first()->id;

    $response = $this->userRequest()
        ->patch("/api/v1/notification", [
            'ids' => [$id]
        ]);

    $response->assertStatus(204);
    $user->load('notifications');
    $user->load('unreadNotifications');
    expect(count($user->notifications))->toEqual(1);
    expect(count($user->unreadNotifications))->toEqual(1);
});

test('delete notification', function () {
    $data = setupData();
    $user = $data['users'][0];

    $user->load('notifications');
    $user->load('unreadNotifications');

    $id = $user->unreadNotifications->first()->id;

    $response = $this->userRequest()
        ->delete("/api/v1/notification/$id");

    $this->assertStatus($response, 204);
    $user->load('notifications');
    $user->load('unreadNotifications');
    expect(count($user->notifications))->toEqual(0);
    expect(count($user->unreadNotifications))->toEqual(0);
});

test('delete comment', function () {
    $data = setupData();
    $user = $data['users'][0];
    $entity = $data['entity'];

    $id = $entity->comments->first()->id;
    $response = $this->userRequest()
        ->delete("/api/v1/comment/$id");

    $entity->load('comments');
    $response->assertStatus(204);
    expect(count($entity->comments))->toEqual(2);
    expect(count($entity->comments()->withoutTrashed()->get()))->toEqual(1);
});
