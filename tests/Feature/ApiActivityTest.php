<?php

use App\Entity;
use App\User;
use Spatie\Activitylog\Models\Activity;


test('get all activity endpoint', function () {
    $actCnt = Activity::count();
    expect($actCnt)->toEqual(0);

    $response = $this->userRequest()
        ->post('/api/v1/entity', [
            'name' => 'Unit-Test Entity I',
            'entity_type_id' => 3,
            'root_entity_id' => 1
        ]);
    $entity = Entity::latest()->first();

    $response = $this->userRequest()
        ->patch('/api/v1/entity/4/attributes', [
            [
                'params' => [
                    'aid' => 19,
                    'cid' => 4
                ],
                'op' => 'add',
                'value' => 'Test'
            ]
        ]);

    $response = $this->userRequest()
        ->patch('/api/v1/entity/4/attributes', [
            [
                'params' => [
                    'aid' => 19,
                    'cid' => 4
                ],
                'op' => 'replace',
                'value' => 'Test2'
            ]
        ]);

    $response = $this->userRequest()
        ->delete('/api/v1/entity/'.$entity->id);

    $response->assertStatus(204);
    $actCnt = Activity::count();
    expect($actCnt)->toBeGreaterThanOrEqual(5);
    expect($actCnt)->toBeLessThanOrEqual(6);

    $response = $this->userRequest()
        ->get('/api/v1/activity');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        [
            'id',
            'log_name',
            'description',
            'subject_id',
            'subject',
            'subject_type',
            'causer_id',
            'causer',
            'properties',
            'created_at',
            'updated_at',
        ]
    ]);
    $response->assertJson([
        [
            'log_name' => 'default',
            'description' => 'created',
            'causer_id' => 1,
            'subject_id' => $entity->id,
            'subject_type' => 'App\\Entity',
        ]
    ]);

    // With start and end before now
    $response = $this->userRequest()
        ->post('/api/v1/activity', [
            'timespan' => [
                'from' => '2017-12-20 09:30:00',
                'to' => '2018-12-20 09:30:00',
            ]
        ]);

    $response->assertStatus(200);
    $content = json_decode($response->getContent());
    expect($content->data)->toEqual([]);

    // With start before now
    $response = $this->userRequest()
        ->post('/api/v1/activity', [
            'timespan' => [
                'from' => '2017-12-20 09:30:00',
            ]
        ]);

    $response->assertStatus(200);
    $content = json_decode($response->getContent());
    expect(count($content->data))->toBeGreaterThanOrEqual(5);
    expect(count($content->data))->toBeLessThanOrEqual(6);

    // With single text search string
    $response = $this->userRequest()
        ->post('/api/v1/activity', [
            'text' => 'tEst'
        ]);

    $response->assertStatus(200);
    $content = json_decode($response->getContent());
    expect(count($content->data))->toEqual(4);

    // With multiple text search strings
    $response = $this->userRequest()
        ->post('/api/v1/activity', [
            'text' => 'unit entity'
        ]);

    $response->assertStatus(200);
    $content = json_decode($response->getContent());
    expect(count($content->data))->toEqual(2);

    // With file only
    $response = $this->userRequest()
        ->post('/api/v1/activity', [
            'text' => 'Entity FooBar'
        ]);

    $response->assertStatus(200);
    $content = json_decode($response->getContent());
    expect(count($content->data))->toEqual(0);

    // With user id=2 and id=3
    $response = $this->userRequest()
        ->post('/api/v1/activity', [
            'users' => [2, 3]
        ]);

    $response->assertStatus(200);
    $content = json_decode($response->getContent());
    expect(count($content->data))->toEqual(0);

    // With user id=1 only
    $response = $this->userRequest()
        ->post('/api/v1/activity', [
            'users' => [1]
        ]);

    $response->assertStatus(200);
    $content = json_decode($response->getContent());
    expect(count($content->data))->toBeGreaterThanOrEqual(5);
    expect(count($content->data))->toBeLessThanOrEqual(6);

    // With user id=1,2,5
    $response = $this->userRequest()
        ->post('/api/v1/activity', [
            'users' => [1, 2, 5]
        ]);

    $response->assertStatus(200);
    $content = json_decode($response->getContent());
    expect(count($content->data))->toBeGreaterThanOrEqual(5);
    expect(count($content->data))->toBeLessThanOrEqual(6);
});

test('permissions', function () {
    User::first()->roles()->detach();

    $calls = [
        ['url' => '/activity', 'error' => 'You do not have the permission to view activity logs', 'verb' => 'get'],
        ['url' => '/activity/2', 'error' => 'You do not have the permission to view activity logs', 'verb' => 'get'],
        ['url' => '/activity', 'error' => 'You do not have the permission to view activity logs', 'verb' => 'post'],
    ];

    $response = null;
    foreach($calls as $c) {
        $response = $this->userRequest()
            ->json($c['verb'], '/api/v1' . $c['url']);

        $response->assertStatus(403);
        $response->assertSimilarJson([
            'error' => $c['error']
        ]);
    }
});

test('exceptions', function () {
    $calls = [
        ['url' => '/activity/99', 'error' => 'This user does not exist', 'verb' => 'get'],
    ];

    foreach($calls as $c) {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->json($c['verb'], '/api/v1' . $c['url']);

        $response->assertStatus(400);
        $response->assertSimilarJson([
            'error' => $c['error']
        ]);
    }
});

test('validations', function () {
    $userError = 'The users must be an array.';

    $response = $this->userRequest()
        ->post('/api/v1/activity', [
            'users' => 1
        ]);

    $this->assertStatus($response, 422);
    $response->assertJson(['message' => $userError, 'errors' => ['users' => [$userError]]]);

    $response = $this->userRequest()
        ->post('/api/v1/activity', [
            'timespan' => '2017-12-20 09:30:00'
        ]);

    expect($response->exception->getMessage())->toEqual('The timespan must be an array.');
});
