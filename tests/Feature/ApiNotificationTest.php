<?php

namespace Tests\Feature;

use App\Entity;
use App\Notifications\CommentPosted;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class ApiNotificationTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCommentAndNotificationEndpoints()
    {
        $user = User::find(1);
        $entity = Entity::first();

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token"
        ])
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
        ]);

        $entity->load('comments');
        $this->assertEquals(1, count($entity->comments));
        $this->assertEquals('This is a test', $entity->comments[0]->content);
        $this->assertEquals('value', $entity->comments[0]->metadata['key']);

        $this->refreshToken($response);
        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token"
        ])
            ->post("/api/v1/comment", [
                'content' => 'This is still a test',
            ]);
        $response->assertStatus(200);
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
        $this->assertEquals(1, count($entity->comments));
        $this->assertEquals('This is still a test', $entity->comments[0]->content);
        $this->assertEquals('value', $entity->comments[0]->metadata['key']);

        $this->refreshToken($response);
        $eid = $entity->id;
        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token"
        ])
            ->get("/api/v1/comment/resource/$eid?r=entity");
        $response->assertStatus(200);
        $response->assertJsonCount(1);
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

        $this->refreshToken($response);
        $cid = $entity->comments[0]->id;
        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token"
        ])
            ->get("/api/v1/comment/$cid/reply");
        $response->assertStatus(200);
        $response->assertJsonCount(0);

        $user->load('notifications');
        $user->load('unreadNotifications');
        $this->assertEquals(1, count($user->notifications));
        $this->assertEquals(1, count($user->unreadNotifications));

        $user->notify(new CommentPosted($entity->comments[0], [], []));
        $user->load('notifications');
        $user->load('unreadNotifications');
        $this->assertEquals(2, count($user->notifications));
        $this->assertEquals(2, count($user->unreadNotifications));

        $id1 = $user->unreadNotifications[0]->id;
        $id2 = $user->unreadNotifications[1]->id;

        $this->refreshToken($response);
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->patch("/api/v1/notification/read/$id1");

        $response->assertStatus(204);
        $user->load('notifications');
        $user->load('unreadNotifications');
        $this->assertEquals(2, count($user->notifications));
        $this->assertEquals(1, count($user->unreadNotifications));

        $this->refreshToken($response);
        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token"
        ])
            ->patch("/api/v1/notification/read");

        $response->assertStatus(204);
        $user->load('notifications');
        $user->load('unreadNotifications');
        $this->assertEquals(2, count($user->notifications));
        $this->assertEquals(0, count($user->unreadNotifications));

        $this->refreshToken($response);
        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token"
        ])
            ->delete("/api/v1/notification/$id2");

        $response->assertStatus(204);
        $user->load('notifications');
        $user->load('unreadNotifications');
        $this->assertEquals(1, count($user->notifications));
        $this->assertEquals(0, count($user->unreadNotifications));

        $this->refreshToken($response);
        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token"
        ])
            ->patch("/api/v1/notification", [
                'ids' => [$id1, $id2]
            ]);

        $response->assertStatus(204);
        $user->load('notifications');
        $user->load('unreadNotifications');
        $this->assertEquals(0, count($user->notifications));
        $this->assertEquals(0, count($user->unreadNotifications));

        $this->refreshToken($response);
        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->token"
        ])
            ->delete("/api/v1/comment/$cid");

        $entity->load('comments');
        $this->assertEquals(0, count($entity->comments));
    }
}
