<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Context;
use App\Geodata;
use App\AttributeValue;

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
        $randomContext = Context::inRandomOrder()->first();

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
        $maxId = Context::max('id') + 100;
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

    public function testPutPossibility() {
        $this->withoutMiddleware(); // ignore JWT-Auth

        // Testing $app->put('attribute_value/{cid:[0-9]+}/{aid:[0-9]+}', 'ContextController@putPossibility');
        $av = AttributeValue::inRandomOrder()->first();
        $newPos = $this->faker->numberBetween(1, 100);
        $parameters = [
            'possibility' => $newPos,
            'possibility_description' => $this->faker->text
        ];
        $toCheck = array_merge($parameters, [
            'attribute_id' => $av->attribute_id,
            'context_id' => $av->context_id,
            'lasteditor' => $this->user->name
        ]);
        $response = $this->actingAs($this->user)->call('PUT', 'context/attribute_value/'.$av->context_id . '/' . $av->attribute_id, $parameters);

        $this->assertEquals(200, $response->status());
        $this->seeJson($toCheck);
        $this->seeInDatabase('attribute_values', $toCheck);

        $newPos = $this->faker->numberBetween(1, 100);
        $parameters = [
            'possibility' => $newPos
        ];
        $toCheck = array_merge($parameters, [
            'possibility_description' => null,
            'attribute_id' => $av->attribute_id,
            'context_id' => $av->context_id,
            'lasteditor' => $this->user->name
        ]);
        $response = $this->actingAs($this->user)->call('PUT', 'context/attribute_value/'.$av->context_id . '/' . $av->attribute_id, $parameters);

        $this->assertEquals(200, $response->status());
        $this->seeJson($toCheck);
        $this->seeInDatabase('attribute_values', $toCheck);

        $parameters = [
            'possibility' => $this->faker->randomFloat(2, 1, 100)
        ];
        $toCheck = [
            'possibility' => $newPos,
            'possibility_description' => null,
            'attribute_id' => $av->attribute_id,
            'context_id' => $av->context_id,
            'lasteditor' => $this->user->name
        ];
        $response = $this->actingAs($this->user)->call('PUT', 'context/attribute_value/'.$av->context_id . '/' . $av->attribute_id, $parameters);

        $this->assertEquals(422, $response->status());
        $this->seeJsonStructure([
            'error' => [
                'possibility'
            ]
        ]);
        $this->seeJson([
            'message' => 'validation.integer',
            'source' => [
                'pointer' => 'possibility'
            ]
        ]);
        $this->seeInDatabase('attribute_values', $toCheck);
    }

    public function testLinkGeodata() {
        $this->withoutMiddleware(); // ignore JWT-Auth

        // $app->patch('geodata/{cid:[0-9]+}', 'ContextController@linkGeodata');
        // $app->patch('geodata/{cid:[0-9]+}/{gid:[0-9]+}', 'ContextController@linkGeodata');
        $context = Context::whereNotNull('geodata_id')->inRandomOrder()->first();
        $gid = $context->geodata_id;

        $toCheck = [
            'id' => $context->id,
            'name' => $context->name,
            'context_type_id' => $context->context_type_id,
            'root_context_id' => $context->root_context_id,
            'lasteditor' => $this->user->name,
            'geodata_id' => null,
            'rank' => $context->rank
        ];
        // Test Unlink
        $response = $this->actingAs($this->user)->call('PATCH', 'context/geodata/' . $context->id, []);
        $this->assertEquals(200, $response->status());
        $this->seeJsonStructure([
            'context' => [
                'id', 'name', 'context_type_id', 'root_context_id', 'geodata_id', 'rank'
            ]
        ]);
        $this->seeJson($toCheck);
        $this->seeInDatabase('contexts', $toCheck);

        // Test geodata does not exist
        $undefGid = Geodata::max('id') + 100;
        \Log::info("UNDEF: $undefGid");
        $response = $this->actingAs($this->user)->call('PATCH', 'context/geodata/' . $context->id . '/' . $undefGid, []);
        \Log::info($response->content());
        $this->assertEquals(200, $response->status());
        $this->seeJsonEquals([
            'error' => 'This geodata does not exist'
        ]);
        $this->seeInDatabase('contexts', $toCheck);

        // Test Link
        $toCheck['geodata_id'] = strval($gid);
        $response = $this->actingAs($this->user)->call('PATCH', 'context/geodata/' . $context->id . '/' . $gid, []);
        $this->assertEquals(200, $response->status());
        $this->seeJsonStructure([
            'context' => [
                'id', 'name', 'context_type_id', 'root_context_id', 'geodata_id', 'rank'
            ]
        ]);
        $this->seeJson($toCheck);
        $this->seeInDatabase('contexts', $toCheck);

        // Test context does not exist
        $undefCid = Context::max('id') + 100;
        $toCheck['geodata_id'] = $context->geodata_id;
        $response = $this->actingAs($this->user)->call('PATCH', 'context/geodata/' . $undefCid . '/' . $gid, []);
        $this->assertEquals(200, $response->status());
        $this->seeJsonEquals([
            'error' => 'This context does not exist'
        ]);

        // Test already linked
        $toCheck['geodata_id'] = $context->geodata_id;
        $response = $this->actingAs($this->user)->call('PATCH', 'context/geodata/' . $context->id . '/' . $gid, []);
        $this->assertEquals(200, $response->status());
        $this->seeJsonEquals([
            'error' => 'This context is already linked to a geodata'
        ]);
        $this->seeInDatabase('contexts', $toCheck);
    }

    public function testDuplicate() {
        $this->withoutMiddleware(); // ignore JWT-Auth

        // $app->post('{id:[0-9]+}/duplicate', 'ContextController@duplicate');
        $context = Context::inRandomOrder()->first();

        $toCheck = [
            'id' => $context->id,
            'name' => $context->name,
            'context_type_id' => $context->context_type_id,
            'root_context_id' => $context->root_context_id,
            'lasteditor' => $context->lasteditor,
            'geodata_id' => $context->geodata_id,
            'rank' => $context->rank
        ];

        $response = $this->actingAs($this->user)->call('POST', 'context/' . $context->id . '/duplicate', []);
        $this->assertEquals(200, $response->status());
        $this->seeJsonStructure([
            'obj' => [
                'id', 'name', 'context_type_id', 'root_context_id', 'geodata_id', 'rank'
            ]
        ]);
        $this->seeInDatabase('contexts', $toCheck);
        $toCheck['geodata_id'] = null;
        $toCheck['id'] = Context::max('id'); // Duplicate should be last inserted
        $toCheck['rank'] = $toCheck['rank'] + 1; //Duplicate should be inserted after original element in the tree
        $toCheck['name'] = $toCheck['name'] . ' (1)';
        $this->seeJson($toCheck);
        $this->seeInDatabase('contexts', $toCheck);

        $undefCid = Context::max('id') + 100;
        $response = $this->actingAs($this->user)->call('POST', 'context/' . $undefCid . '/duplicate', []);
        $this->assertEquals(200, $response->status());
        $this->seeJsonEquals([
            'error' => 'This context does not exist'
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
