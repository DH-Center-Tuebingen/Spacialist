<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Literature;

class LiteratureControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testLiterature() {
        $this->withoutMiddleware(); // ignore JWT-Auth

        $mock = factory('App\Literature')->make();
        $parameters = [];
        $structure = [];
        foreach($mock->attributesToArray() as $key => $value) {
            $parameters[$key] = $value;
            $structure[] = $key;
        }

        // Test insert $app->post('', 'LiteratureController@add');
        $response = $this->actingAs($this->user)->call('POST', 'literature', $parameters);
        // Assertions for insert
        $this->assertEquals(200, $response->status());
        $this->seeJsonStructure([
            'literature' => $structure
        ]);
        $toCheck = array_merge($parameters, ['lasteditor' => $this->user->name]);
        $this->seeJson($toCheck);
        $this->seeInDatabase('literature', $toCheck);

        // Testing get single Literature $app->get('{id:[0-9]+}', 'LiteratureController@getLiterature');
        $lit = Literature::inRandomOrder()->first();
        $response = $this->actingAs($this->user)->call('GET', 'literature/'.$lit->id);
        $this->assertEquals(200, $response->status());
        $this->seeJsonStructure($structure);
        $toCheck = $lit->attributesToArray();
        $this->seeJsonEquals($toCheck);
        $this->seeInDatabase('literature', $toCheck);

        // Testing edit $app->patch('{id:[0-9]+}', 'LiteratureController@edit');
        $author = $this->faker->name;
        $title = $this->faker->words(3, true);
        $parameters = [
            'author' => $author,
            'title' => $title
        ];
        $response = $this->actingAs($this->user)->call('PATCH', 'literature/'.$lit->id, $parameters);
        $this->assertEquals(200, $response->status());
        $this->seeJsonStructure([
            'literature' => $structure
        ]);
        $toCheck = $lit->attributesToArray();
        unset($toCheck['updated_at']);
        unset($toCheck['created_at']);
        $toCheck['author'] = $author;
        $toCheck['title'] = $title;
        $toCheck['lasteditor'] = $this->user->name;
        $this->seeJson($toCheck);
        $this->seeInDatabase('literature', $toCheck);

        // Testing delete $app->delete('{id:[0-9]+}', 'LiteratureController@delete');
        $response = $this->actingAs($this->user)->call('DELETE', 'literature/'.$lit->id);
        $this->assertEquals(200, $response->status());
        $this->missingFromDatabase('literature', $toCheck);
    }
}
