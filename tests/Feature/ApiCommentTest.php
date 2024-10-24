<?php

namespace Tests\Feature;

use App\Comment;
use Tests\TestCase;

use Carbon\Carbon;
use Exception;
use Tests\Permission;
use Tests\ResponseTester;

class ApiCommentTest extends TestCase
{

    // ==========================================
    //              [[ GET ]]
    // ==========================================

    /**
    * @dataProvider getProvider
    * @testdox GET    /api/v1/comment/resource/{id} - Get comments
    */
    public function testGetComments($url, $result) {
        $response = $this->userRequest()
            ->get("/api/v1/comment/$url");

        $this->assertStatus($response, 200);
        $response->assertJsonCount(count($result));
        $response->assertJson($result);
    }

    public static function getProvider() {
        return [
            "Entity" => [
                "resource/1?r=entity", [
                    [
                        "user_id" => 1,
                        "content" => "(1) This is a comment on an entity by user admin without any mentions."
                    ],[
                        "user_id" => 2,
                        "reply_to" => 1,
                        "content" => "(2) This is a reply from John Doe (2) to the previous comment (1) of the @admin with a mention."
                    ],[
                        "user_id" => 2,
                        "content" => "(3) This is a regular post of John Doe (2) on the same entity."
                    ]
                ]
            ],
            "Attribute Value" => [
                "resource/1?r=attribute_value&aid=15",
                [
                    [
                        "user_id" => 2,
                        "reply_to" => NULL,
                        'content' => '(4) This is John Doe on attribute value (1)',
                        'created_at' => '2017-12-20T09:53:35.000000Z',
                        'updated_at' => '2017-12-20T09:53:35.000000Z',
                    ],[
                        "user_id" => 1,
                        "reply_to" => 4,
                        'content' => '(5) This is admin (1) replying to John Doe (2) on attribute value (1)',
                        'created_at' => '2017-12-20T09:53:35.000000Z',
                        'updated_at' => '2017-12-20T09:53:35.000000Z',
                    ],[
                        "user_id" => 1,
                        "reply_to" => NULL,
                        'content' => '(6) This is admin (1) on attribute value (1)',
                        'created_at' => '2017-12-20T09:53:35.000000Z',
                        'updated_at' => '2017-12-20T09:53:35.000000Z',
                    ]
                ]
            ],
            "Replies" => [
                "1/reply",
                [
                    [
                        'content' => '(2) This is a reply from John Doe (2) to the previous comment (1) of the @admin with a mention.',
                    ]
                ]
            ]
        ];
    }

    // ==========================================
    //              [[ POST ]]
    // ==========================================

    /**
    * @dataProvider postProvider
    * @testdox POST   /api/v1/comment - Post request
    */
    public function testAddComment($url, $input, $result) {
        $response = $this->userRequest()
            ->post("/api/v1/comment", $input);

        $this->assertStatus($response, 201);
        $getResponse = $this->userRequest($response)
            ->get("/api/v1/comment/resource/$url");

        $this->assertStatus($getResponse, 200);
        $content = json_decode($getResponse->getContent());
        $getResponse->assertJson($result);
    }

    public static function postProvider() {
        return [
            "Attribute Comment" => [
                "8?r=attribute_value&aid=5",
                [
                    'content' => 'This is an attribute_value',
                    'resource_type' => 'attribute_value',
                    'resource_id' => 73,
                    'metadata' => [],
                ],[
                    ['content' => 'This is an attribute_value'],
                ]
                ],
            "Entity Comment" => [
                "2?r=entity",
                [
                    'content' => 'This is an entitiy',
                    'resource_type' => 'entity',
                    'resource_id' => 2,
                    'metadata' => [],
                ],[
                    ['content' => 'This is an entitiy'],
                ]
            ],
            "Attribute Comment With Certainty Change" => [
                "8?r=attribute_value&aid=5",
                [
                    'content' => 'This is an attribute_value',
                    'resource_type' => 'attribute_value',
                    'resource_id' => 73,
                    'metadata' => [
                        "certainty_from" => 100,
                        "certainty_to"   => 37
                    ],
                ],[
                    [
                        'content' => 'This is an attribute_value',
                        'metadata' => [
                            'certainty_to' => 37,
                            'certainty_from' => 100,
                        ]
                    ],
                ]
            ],
            // "Reply To Comment" => [
            //     "1?r=entity",
            //     [
            //         'content' => 'This is an entitiy',
            //         'resource_type' => 'entity',
            //         'resource_id' => 1,
            //         'metadata' => [],
            //         'reply_to' => 2
            //     ],[
            //         ['content' => '(1) This is a comment on an entity by user admin without any mentions.'],
            //         ['content' => '(2) This is a reply from John Doe (2) to the previous comment (1) of the @admin with a mention.'],
            //         ['content' => '(3) This is a regular post of John Doe (2) on the same entity.'],
            //         [
            //             'content' => 'This is an entitiy',
            //             "reply_to" => 2,
            //         ],
            //     ]
            // ]
        ];
    }

    // ==========================================
    //              [[ PATCH ]]
    // ==========================================

    /**
    * @dataProvider patchProvider
    * @testdox PATCH  /api/v1/comment - Patch request
    */
    public function testEditComment($id, $url, $input, $result) {
        $response = $this->userRequest()
            ->patch("/api/v1/comment/$id", $input);

        $this->assertStatus($response, 200);
        $getResponse = $this->userRequest($response)
            ->get("/api/v1/comment/resource/$url");

        $this->assertStatus($getResponse, 200);
        $content = json_decode($getResponse->getContent());
        $getResponse->assertJson($result);

        /**
         * As the target comment is not always the first element in the array
         * we need to find the index of the target comment in the array
         * the requirement for this is, that every item has a created_at
         * set otherwise this will fail.
         */
        $idx = 0;
        while(!isset($result[$idx]["created_at"])) {
            $idx ++;
            if($idx > 10) throw new Exception("Out Of Range");
        }

        $updatedAt = Carbon::parse($content[$idx]->updated_at);
        $this->assertTrue(
            $updatedAt->diffInSeconds(Carbon::now()) < 5,
            "The 'updated_at' value is not withing 5s of the current time"
        );
    }

    public static function patchProvider() {
        return [
            "Change Comment On Entity" => [
                1,
                "1?r=entity",
                [
                    "content" => "Patched content"
                ],
                [
                    [
                        "content" => "Patched content",
                        "created_at" => "2017-12-20T09:47:35.000000Z"
                    ]
                ]
            ],
            "Change Comment On Attribute Value" => [
                5,
                "1?r=attribute_value&aid=15",
                [
                    "content" => "Patched content"
                ],
                [
                    [],
                    [
                        "content" => "Patched content",
                        "created_at" => "2017-12-20T09:53:35.000000Z"
                    ]
                ]
            ]
        ];
    }

    // ==========================================
    //              [[ DELETE ]]
    // ==========================================

    /**
    * @dataProvider deleteProvider
    * @testdox DELETE /api/v1/comment/{id} - Delete request
    */
    public function testDeleteComment($id, $targetCount)
    {
        $response = $this->userRequest()
            ->delete('/api/v1/comment/' . $id);

        $response->assertStatus(204);
        $cnt = Comment::withoutTrashed()->count();
        $this->assertEquals($targetCount, $cnt);
    }

    public static function deleteProvider() {
        return [
            "Delete Entity Comment" => [3, 5],
            "Delete Attribute Value Comment" => [6, 5],
            "Delete Entity Comment Which Was Replied To" => [1, 4],
            "Delete AV Comment Which Was Replied To" => [4, 4]
        ];
    }

    // ==========================================
    //      [[ ADDITIONAL DATA PROVIDERS ]]
    // ==========================================

    /**
     * @dataProvider permissions
     * @testdox [[PROVIDER]] Routes Without Permissions
     */
    public function testWithoutPermission($permission) {
        (new ResponseTester($this))->testMissingPermission($permission);
    }
    /**
     * @dataProvider exceptions
     * @testdox [[PROVIDER]] Exceptions With Permissions
     */
    public function testExceptions($permission) {
        (new ResponseTester($this))->testExceptions($permission);
    }

    /**
     * @dataProvider unprocessable
     * @testdox [[PROVIDER]] Unprocessable Entities
     */
    public function testUnprocessable($permission, $errors) {
        $response = $this->userRequest()
        ->json($permission->getMethod(), $permission->getUrl(), $permission->getData());

        $this->assertStatus($response, 422);
        $response->assertJson([
            'errors' => $errors
        ]);
    }

    public static $testComment =  ['content' => 'Test Comment'];

    public static function permissions() {
        return [
            "GET    /api/v1/comment/resource/1?r=entity"                    => Permission::for("get",   "/api/v1/comment/resource/1?r=entity",                  "You do not have the permission to get comments"),
            "GET    /api/v1/comment/resource/8?r=attribute_value&aid=5"     => Permission::for("get",   "/api/v1/comment/resource/8?r=attribute_value&aid=5",   "You do not have the permission to get comments"),
            "POST   /api/v1/comment"                                        => Permission::for("post",  "/api/v1/comment",                                      "You do not have the permission to add comments", self::$testComment),
            "PATCH  /api/v1/comment/1"                                      => Permission::for("patch", "/api/v1/comment/1",                                    "You do not have the permission to edit a comment", self::$testComment),
            "DELETE /api/v1/comment/1"                                      => Permission::for("delete","/api/v1/comment/1",                                      "You do not have the permission to delete a comment"),
        ];
    }

    //TODO:: Test certainties
    public static function exceptions() {
        return [
            "GET    /api/v1/comment/resource/99?r=entity -> invalid entity"                         => Permission::for("get",   "/api/v1/comment/resource/99?r=entity",                     "This entity does not exist"),
            "GET    /api/v1/comment/resource/99?r=attribute_value&aid=5 -> invalid entity"          => Permission::for("get",   "/api/v1/comment/resource/99?r=attribute_value&aid=5",      "This attribute value does not exist"),
            "GET    /api/v1/comment/resource/8?r=attribute_value&aid=99 -> invalid attribute"       => Permission::for("get",   "/api/v1/comment/resource/8?r=attribute_value&aid=99",      "This attribute value does not exist"),
            "GET    /api/v1/comment/resource/1?r=attribute_value&aid=17 -> no attribute value"      => Permission::for("get",   "/api/v1/comment/resource/1?r=attribute_value&aid=17",      "This attribute value does not exist"),
             // DISCUSS: I would expect a comment to fail if it is empty. But due to the nature of
            //          the comments being intertwined with the certainty system, this is currently not possible.
            // "POST   /api/v1/comment -> empty content"                                               => Permission::for("post",  "/api/v1/comment",                                          "The resource_id field is required.", ['content' => '', 'resource_id' => 1, 'resource_type' => 'entity']),
            "PATCH  /api/v1/comment/99 -> comment does not exist"                                   => Permission::for("patch", "/api/v1/comment/99",                                       "This comment does not exist", self::$testComment),
            "PATCH  /api/v1/comment/99 -> comment does not exist"                                   => Permission::for("patch", "/api/v1/comment/99",                                       "This comment does not exist", self::$testComment),
            "PATCH  /api/v1/comment/99 -> comment does not exist"                                   => Permission::for("patch", "/api/v1/comment/99",                                       "This comment does not exist", self::$testComment),
            "DELETE /api/v1/comment/99 -> comment does not exist"                                   => Permission::for("delete","/api/v1/comment/99",                                       "This comment does not exist"),
        ];
    }

    public static function unprocessable() {
        return [
            "POST   /api/v1/comment -> missing resource_id & _type"                                 => [new Permission("post",  "/api/v1/comment","", ['content' => 'content']), ["resource_id" => ["The resource id field is required."], "resource_type" => ["The resource type field is required."]]],
            "POST   /api/v1/comment -> missing resource_id"                                       => [new Permission("post",  "/api/v1/comment","", ['content' => 'content', 'resource_id' => 1]),  [ "resource_type" => ["The resource type field is required."]]],
            "POST   /api/v1/comment -> missing resource_type"                                         => [new Permission("post",  "/api/v1/comment","", ['content' => 'content', 'resource_type' => 'entity']),  ["resource_id" => ["The resource id field is required."]]],
        ];
    }
}