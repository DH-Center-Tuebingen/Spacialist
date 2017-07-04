<?php

use Laravel\Lumen\Testing\DatabaseTransactions;

class ContextControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testAddContext() {
        $this->withoutMiddleware();
        $user = factory(App\User::class)->make([
            'name' => 'Test User',
            'email' => 'test1234@user.com'
        ]);
        $user->save();
        $user->attachRole(App\Role::where('name', 'admin')->first());

        $mock = factory('App\Context')->make();

        $parameters = [
            'name' => $mock->name,
            'context_type_id' => $mock->context_type_id
        ];
        if($mock->root_context_id) $parameters['root_context_id'] = $mock->root_context_id;

        $response = $this->actingAs($user)->call('POST', 'context', $parameters);

        $toCheck = array_merge($parameters, [
            'lasteditor' => $user->name
        ]);

        $this->assertEquals(200, $response->status());
        $this->seeJsonStructure([
            'context' => [
                "context_type_id",
                "created_at",
                "id",
                "lasteditor",
                "name",
                "rank",
                "updated_at"
            ]
        ]);
        $this->seeJson($toCheck);
        $this->seeInDatabase('contexts', $toCheck);

        // $response = $this->actingAs($user)->call('DELETE', 'context/')
    }

    // public function testEditorSearch()
    // {
    //     $user = factory('App\User')->create();
    //
    //     $this->assertEquals(
    //         $this->actingAs($user)->post('editor/search', [
    //             'val' => 'komm'
    //         ]),
    //         $this->actingAs($user)->post('editor/search', [
    //             'val' => 'komm',
    //             'lang' => 'de'
    //         ])
    //     );
    // }
}
