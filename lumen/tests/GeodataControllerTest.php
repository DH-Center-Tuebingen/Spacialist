<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Geodata;
use Phaza\LaravelPostgis\Geometries\Point;
use Phaza\LaravelPostgis\Geometries\LineString;
use Phaza\LaravelPostgis\Geometries\Polygon;

class GeodataControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testGeodata() {
        $this->withoutMiddleware(); // ignore JWT-Auth

        // TODO add (random) tests for all different types
        $coords = [
            'lat' => 9.12,
            'lng' => 45.31
        ];
        $type = 'Point';
        $parameters = [
            'coords' => json_encode([$coords]),
            'type' => $type
        ];

        // Test insert $app->post('', 'GeodataController@add');
        $response = $this->actingAs($this->user)->call('POST', 'geodata', $parameters);
        // Assertions for insert
        \Log::info($response->content());
        $this->assertEquals(200, $response->status());
        $this->seeJsonStructure([
            'geodata' => [
                'geodata', 'id'
            ]
        ]);
        // TODO check PostGIS structure

        // Testing delete
        $gd = Geodata::inRandomOrder()->first();
        $response = $this->actingAs($this->user)->call('DELETE', 'geodata/' . $gd->id);
        $this->assertEquals(200, $response->status());
        $this->missingFromDatabase('geodata', [
            'id' => $gd->id
        ]);
    }
}
