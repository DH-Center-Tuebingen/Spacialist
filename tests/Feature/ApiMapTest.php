<?php

namespace Tests\Feature;

use Tests\TestCase;
// use Illuminate\Contracts\Filesystem\FileNotFoundException;
// use Illuminate\Foundation\Testing\WithoutMiddleware;
// use Illuminate\Http\UploadedFile;
// use Illuminate\Support\Facades\Storage;

class ApiMapTest extends TestCase
{
    private static $layerAttributes = [
        'id',
        'name',
        'url',
        'type',
        'subdomains',
        'attribution',
        'opacity',
        'layers',
        'styles',
        'format',
        'version',
        'visible',
        'is_overlay',
        'api_key',
        'layer_type',
        'position',
        'entity_type_id',
        'color',
        'created_at',
        'updated_at',
    ];

    private static $geodataAttributes = [
        'id',
        'geom',
        'color',
        'lasteditor',
        'created_at',
        'updated_at',
    ];

    // Testing GET requests

    /**
     * Test get all data (layers and geodata).
     *
     * @return void
     */
    public function testGetDataEndpoint()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/map');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'layers' => [
                '*' => self::$layerAttributes
            ],
            'geodata' => [
                '*' => self::$geodataAttributes
            ],
        ]);
        $content = $response->decodeResponseJson();
        $this->assertEquals(7, count($content['layers']));
        $this->assertEquals(3, count($content['geodata']));
    }

    /**
     * Test get all layers.
     *
     * @return void
     */
    public function testGetAllLayersEndpoint()
    {
        // basic aka not-entity-type layers only
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/map/layer?basic=true');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'baselayers' => [
                '*' => self::$layerAttributes
            ],
            'overlays' => [
                '*' => self::$layerAttributes
            ],
        ]);
        $response->assertJson([
            'baselayers' => [
                ['id' => 9],
            ],
            'overlays' => [],
        ]);

        // basic aka not-entity-type layers only (as dictionary)
        $this->refreshToken($response);
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/map/layer?basic=true&d=true');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'baselayers' => [
                '*' => self::$layerAttributes
            ],
            'overlays' => [
                '*' => self::$layerAttributes
            ],
        ]);
        $response->assertJson([
            'baselayers' => [
                '9' => ['id' => 9],
            ],
            'overlays' => [],
        ]);

        // all layers
        $this->refreshToken($response);
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/map/layer');

        $response->assertStatus(200);
        $response->assertJson([
            'baselayers' => [
                ['id' => 9],
            ],
            'overlays' => [
                ['id' => 1],
                ['id' => 4],
                ['id' => 5],
                ['id' => 6],
                ['id' => 7],
                ['id' => 8],
            ],
        ]);
    }

    /**
     * Test get all entity-type layers.
     *
     * @return void
     */
    public function testGetEntityTypeLayersEndpoint()
    {
        // basic aka not-entity-type layers only
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/map/layer/entity');

        $response->assertStatus(200);
        $response->assertJsonCount(6);
        $response->assertJsonStructure([
            '*' => self::$layerAttributes
        ]);
        $response->assertJson([
            ['id' => 1],
            ['id' => 4],
            ['id' => 5],
            ['id' => 6],
            ['id' => 7],
            ['id' => 8],
        ]);
    }

    /**
     * Test get a layer (id=5)
     *
     * @return void
     */
    public function testGetLayerEndpoint()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/map/layer/5');

        $response->assertStatus(200);
        $response->assertJsonStructure(
            array_merge(
                self::$layerAttributes,
                [
                    'entity_type'
                ]
            )
        );
        $response->assertExactJson([
            'id' => 5,
            'name' => '',
            'url' => '',
            'type' => 'Polygon',
            'subdomains' => null,
            'attribution' => null,
            'opacity' => "1",
            'layers' => null,
            'styles' => null,
            'format' => null,
            'version' => null,
            'visible' => true,
            'is_overlay' => true,
            'api_key' => null,
            'layer_type' => null,
            'position' => 3,
            'entity_type_id' => 4,
            'entity_type' => [
                'id' => 4,
                'thesaurus_url' => 'https://spacialist.escience.uni-tuebingen.de/<user-project>/befund#20171220094916',
                'is_root' => false,
                'created_at' => '2017-12-20 10:03:15',
                'updated_at' => '2017-12-20 10:03:15',
            ],
            'color' => '#3E798B',
            'created_at' => '2017-12-20 10:03:15',
            'updated_at' => '2017-12-20 10:03:15',
        ]);
    }

    /**
     * Test get geometries of a layer (id=4)
     *
     * @return void
     */
    public function testGetLayerGeometriesEndpoint()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/map/layer/4/geometry');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
        $response->assertJsonStructure([
            '*' => array_merge(
                self::$geodataAttributes,
                [
                    'entity'
                ]
            )
        ]);
        $response->assertJson([
            [
                'id' => 2,
                'entity' => [
                    'id' => 1
                ]
            ],
            [
                'id' => 3,
                'entity' => [
                    'id' => 7
                ]
            ],
        ]);
    }

    /**
     * Test get definition of an srid code (EPSG:4826 (WGS84))
     *
     * @return void
     */
    public function testGetEpsgEndpoint()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/map/epsg/4826');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'srid',
            'auth_name',
            'auth_srid',
            'srtext',
            'proj4text',
        ]);
        $response->assertExactJson([
            'srid' => 4826,
            'auth_name' => 'EPSG',
            'auth_srid' => 4826,
            'srtext' => 'PROJCS["WGS 84 / Cape Verde National",GEOGCS["WGS 84",DATUM["WGS_1984",SPHEROID["WGS 84",6378137,298.257223563,AUTHORITY["EPSG","7030"]],AUTHORITY["EPSG","6326"]],PRIMEM["Greenwich",0,AUTHORITY["EPSG","8901"]],UNIT["degree",0.0174532925199433,AUTHORITY["EPSG","9122"]],AUTHORITY["EPSG","4326"]],PROJECTION["Lambert_Conformal_Conic_2SP"],PARAMETER["standard_parallel_1",15],PARAMETER["standard_parallel_2",16.66666666666667],PARAMETER["latitude_of_origin",15.83333333333333],PARAMETER["central_meridian",-24],PARAMETER["false_easting",161587.83],PARAMETER["false_northing",128511.202],UNIT["metre",1,AUTHORITY["EPSG","9001"]],AXIS["M",EAST],AXIS["P",NORTH],AUTHORITY["EPSG","4826"]]',
            'proj4text' => '+proj=lcc +lat_1=15 +lat_2=16.66666666666667 +lat_0=15.83333333333333 +lon_0=-24 +x_0=161587.83 +y_0=128511.202 +datum=WGS84 +units=m +no_defs ',
        ]);
    }
}
