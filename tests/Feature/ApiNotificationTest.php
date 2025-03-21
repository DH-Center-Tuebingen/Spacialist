<?php

namespace Tests\Feature;

use App\Entity;
use App\Notifications\CommentPosted;
use App\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class ApiNotificationTest extends TestCase
{
    public static function setupData() {
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

    /**
     * @testdox GET    /api/v1/comment/resource/{id}?r=entity : Test get Comments for Resource
     *
     * @return void
     */
    public function testResourceComments()
    {
        $data = self::setupData();
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
    }

    /**
     * @testdox GET    /api/v1/comment/{id}/reply : Test get replies for comment
     *
     * @return void
     */
    public function testCommentReplies()
    {
        $data = self::setupData();
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
        $this->assertEquals(2, count($entity->comments));
        $this->assertEquals(1, count($entity->comments[1]->replies));
    }

    /**
     * @testdox POST   /api/v1/comment : Test Add Comment
     *
     * @return void
     */
    public function testAddComment()
    {
        $data = self::setupData();
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
        $this->assertEquals(3, count($entity->comments));
        $this->assertEquals('This is a test', $entity->comments[2]->content);
        $this->assertEquals('value', $entity->comments[2]->metadata['key']);
    }

    /**
     * @testdox PATCH  /api/v1/comment/{id} : Test Edit Comment
     *
     * @return void
     */
    public function testUpdateComment()
    {
        $data = self::setupData();
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
        $this->assertEquals(2, count($entity->comments));
        $this->assertEquals('This is still a test', $entity->comments[0]->content);
    }

    /**
     * @testdox PATCH  /api/v1/notification/read/{id} : Test mark single notification as read
     *
     * @return void
     */
    public function testMarkNotificationAsRead()
    {
        $data = self::setupData();
        $user = $data['users'][0];

        $user->load('notifications');
        $user->load('unreadNotifications');
        $this->assertEquals(1, count($user->notifications));
        $this->assertEquals(1, count($user->unreadNotifications));

        $id = $user->unreadNotifications[0]->id;

        $response = $this->userRequest()
            ->patch("/api/v1/notification/read/$id");

        $response->assertStatus(204);
        $user->load('notifications');
        $user->load('unreadNotifications');
        $this->assertEquals(1, count($user->notifications));
        $this->assertEquals(0, count($user->unreadNotifications));
    }

    /**
     * @testdox PATCH  /api/v1/notification/read : Test mark array of notifications as read
     *
     * @return void
     */
    public function testMarkNotificationsAsRead()
    {
        $data = self::setupData();
        $user = $data['users'][0];
        $entity = $data['entity'];

        $user->notify(new CommentPosted($entity->comments->last(), [], []));
        $user->load('notifications');
        $user->load('unreadNotifications');
        $this->assertEquals(2, count($user->notifications));
        $this->assertEquals(2, count($user->unreadNotifications));

        $ids = $user->unreadNotifications->pluck('id')->toArray();

        $response = $this->userRequest()
            ->patch("/api/v1/notification/read", [
                'ids' => $ids,
            ]);

        $response->assertStatus(204);
        $user->load('notifications');
        $user->load('unreadNotifications');
        $this->assertEquals(2, count($user->notifications));
        $this->assertEquals(0, count($user->unreadNotifications));
    }

    /**
     * @testdox PATCH  /api/v1/notification : Test delete notifications
     *
     * @return void
     */
    public function testDeleteNotifications()
    {
        $data = self::setupData();
        $user = $data['users'][0];
        $entity = $data['entity'];

        $user->notify(new CommentPosted($entity->comments->last(), [], []));
        $user->load('notifications');
        $user->load('unreadNotifications');
        $this->assertEquals(2, count($user->notifications));
        $this->assertEquals(2, count($user->unreadNotifications));

        $id = $user->unreadNotifications->first()->id;

        $response = $this->userRequest()
            ->patch("/api/v1/notification", [
                'ids' => [$id]
            ]);

        $response->assertStatus(204);
        $user->load('notifications');
        $user->load('unreadNotifications');
        $this->assertEquals(1, count($user->notifications));
        $this->assertEquals(1, count($user->unreadNotifications));
    }

    /**
     * @testdox DELETE /api/v1/notification/{id} : Test delete notification
     *
     * @return void
     */
    public function testDeleteNotification()
    {
        $data = self::setupData();
        $user = $data['users'][0];

        $user->load('notifications');
        $user->load('unreadNotifications');

        $id = $user->unreadNotifications->first()->id;

        $response = $this->userRequest()
            ->delete("/api/v1/notification/$id");

        $this->assertStatus($response, 204);
        $user->load('notifications');
        $user->load('unreadNotifications');
        $this->assertEquals(0, count($user->notifications));
        $this->assertEquals(0, count($user->unreadNotifications));
    }

    /**
     * @testdox DELETE /api/v1/comment/{id} : Test delete comment
     *
     * @return void
     */
    public function testDeleteComment()
    {
        $data = self::setupData();
        $user = $data['users'][0];
        $entity = $data['entity'];

        $id = $entity->comments->first()->id;
        $response = $this->userRequest()
            ->delete("/api/v1/comment/$id");

        $entity->load('comments');
        $response->assertStatus(204);
        $this->assertEquals(2, count($entity->comments));
        $this->assertEquals(1, count($entity->comments()->withoutTrashed()->get()));
    }
}
