<?php

use Laravel\Lumen\Testing\DatabaseTransactions;

class ContextControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected $contextFields = [
        'context_type_id',
        'created_at',
        'id',
        'lasteditor',
        'name',
        'rank',
        'updated_at'
    ];

    public function testAddEditDeleteContext() {
        $this->withoutMiddleware(); // ignore JWT-Auth

        // Get random Context (called at this point to ensure it's not the mock Context)
        $randomContext = App\Context::inRandomOrder()->first();

        // Create Context to test insert
        // $app->post('', 'ContextController@add');
        $mock = factory('App\Context')->make();
        $parameters = [
            'name' => $mock->name,
            'context_type_id' => $mock->context_type_id
        ];
        if($mock->root_context_id) $parameters['root_context_id'] = $mock->root_context_id;

        $response = $this->actingAs($this->user)->call('POST', 'context', $parameters);

        $toCheck = array_merge($parameters, [
            'lasteditor' => $this->user->name
        ]);

        // Assertions for insert
        $this->assertEquals(200, $response->status());
        $this->seeJsonStructure([
            'context' => $this->contextFields
        ]);
        $this->seeJson($toCheck);
        $this->seeInDatabase('contexts', $toCheck);

        // Testing Edit
        // $app->put('{id:[0-9]+}', 'ContextController@put');
        $name = $randomContext->name . ' MODIFIED';
        $response = $this->actingAs($this->user)->call('PUT', 'context/'.$randomContext->id, [
            'name' => $name
        ]);
        $this->assertEquals(200, $response->status());
        $this->seeJsonStructure([
            'context' => $this->contextFields
        ]);
        $this->seeJson([
            'id' => $randomContext->id,
            'context_type_id' => $randomContext->context_type_id,
            'lasteditor' => $this->user->name,
            'name' => $name,
            'rank' => $randomContext->rank
        ]);
        $this->seeInDatabase('contexts', [
            'id' => $randomContext->id,
            'name' => $name,
            'lasteditor' => $this->user->name
        ]);

        // Test Edit with invalid ID
        $maxId = App\Context::max('id') + 100;
        $response = $this->actingAs($this->user)->call('PUT', 'context/' . $maxId, []);
        $this->assertEquals(200, $response->status());
        $this->seeJsonEquals([
            'error' => 'This context does not exist'
        ]);

        // Testing Delete with random Context
        // $app->delete('{id:[0-9]+}', 'ContextController@delete');
        $response = $this->actingAs($this->user)->call('DELETE', 'context/' . $randomContext->id);
        $this->assertEquals(200, $response->status());
        $this->seeInDatabase('contexts', $toCheck);
        $this->missingFromDatabase('contexts', [
            'id' => $randomContext->id
        ]);
    }

    // public function testEditorSearch()
    // {
    //     $this->user = factory('App\User')->create();
    //
    //     $this->assertEquals(
    //         $this->actingAs($this->user)->post('editor/search', [
    //             'val' => 'komm'
    //         ]),
    //         $this->actingAs($this->user)->post('editor/search', [
    //             'val' => 'komm',
    //             'lang' => 'de'
    //         ])
    //     );
    // }
}
