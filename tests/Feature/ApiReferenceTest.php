<?php

use App\Reference;
use Tests\Permission;
use Tests\ResponseTester;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;


test('entity references endpoint', function () {
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
});

test('new reference endpoint', function () {
    $cnt = Reference::count();
    expect(3)->toEqual($cnt);

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
    expect(4)->toEqual($cnt);
});

test('patch reference endpoint', function () {
    $reference = Reference::find(2);
    expect($reference->entity_id)->toEqual(1);
    expect($reference->attribute_id)->toEqual(15);
    expect($reference->bibliography_id)->toEqual(1319);
    expect($reference->description)->toEqual('Picture on left side of page 12');

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
});

test('patch reference missing description endpoint', function () {
    $reference = Reference::find(2);
    expect($reference->entity_id)->toEqual(1);
    expect($reference->attribute_id)->toEqual(15);
    expect($reference->bibliography_id)->toEqual(1319);
    expect($reference->description)->toEqual('Picture on left side of page 12');

    $response = $this->userRequest()
    ->patch('/api/v1/entity/reference/2');

    $response->assertStatus(422);
});

test('delete reference endpoint', function () {
    $cnt = Reference::count();
    expect(3)->toEqual($cnt);

    $response = $this->userRequest()
        ->delete('/api/v1/entity/reference/1');

    $response->assertStatus(204);

    $cnt = Reference::count();
    expect(2)->toEqual($cnt);
});

test('without permission', function ($permission) {
    (new ResponseTester($this))->testMissingPermission($permission);
})->with('permissions');

test('succeed with permission', function ($permission) {
    (new ResponseTester($this))->testExceptions($permission);
})->with('exceptions');

dataset('permissions', function () {
    return [
        "GET    /api/v1/entity/99/reference"    => Permission::for("get",      "/api/v1/entity/99/reference",      "You do not have the permission to view references"),
        "POST   /api/v1/entity/99/reference/99" => Permission::for("post",     "/api/v1/entity/99/reference/99",   "You do not have the permission to add references"),
        "PATCH  /api/v1/entity/reference/99"    => Permission::for("patch",    "/api/v1/entity/reference/99",      "You do not have the permission to edit references"),
        "DELETE /api/v1/entity/reference/99"    => Permission::for("delete",   "/api/v1/entity/reference/99",      "You do not have the permission to delete references"),
    ];
});

dataset('exceptions', function () {
    return [
        "GET    /api/v1/entity/99/reference" =>Permission::for("get",      "/api/v1/entity/99/reference",      "This entity does not exist"),
        "POST   /api/v1/entity/99/reference/99" =>Permission::for("post",     "/api/v1/entity/99/reference/99",   "This entity does not exist", ["bibliography_id" => 1322, "description" => "This is a simple test"]),
        "POST   /api/v1/entity/1/reference/99" =>Permission::for("post",     "/api/v1/entity/1/reference/99",      "This attribute does not exist", ["bibliography_id" => 1322, "description" => "This is a simple test"]),
        "PATCH  /api/v1/entity/reference/99" =>Permission::for("patch",    "/api/v1/entity/reference/99",      "This reference does not exist", ["description" => "This is a simple test"]),
        "DELETE /api/v1/entity/reference/99" =>Permission::for("delete",   "/api/v1/entity/reference/99",      "This reference does not exist"),
    ];
});
