<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

use App\EntityAttribute;

class ApiEditorTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testEntityOccurCountEndpoint()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/editor/dm/entity_type/occurrence_count/5');

        $response->assertStatus(200);
        $response->assertExactJson([3]);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testAttributeOccurCountEndpoint()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/editor/dm/attribute/occurrence_count/9');

        $response->assertStatus(200);
        $response->assertExactJson([4]);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testAttributeOfEntityTypeOccurCountEndpoint()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/editor/dm/attribute/occurrence_count/9/5');

        $response->assertStatus(200);
        $response->assertExactJson([3]);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testTopEntityTypeCountEndpoint()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/editor/dm/entity_type/top');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
        $response->assertJsonStructure([
            [
                'id',
                'thesaurus_url',
                'is_root',
                'created_at',
                'updated_at'
            ]
        ]);
        $response->assertExactJson([
            [
                'id' => 3,
                'thesaurus_url' => 'https://spacialist.escience.uni-tuebingen.de/<user-project>/fundstelle#20171220094911',
                'is_root' => true,
                'created_at' => '2017-12-20 10:03:06',
                'updated_at' => '2017-12-20 10:03:06'
            ],
            [
                'id' => 7,
                'thesaurus_url' => 'https://spacialist.escience.uni-tuebingen.de/<user-project>/lagerstatte#20171220165727',
                'is_root' => true,
                'created_at' => '2017-12-20 16:57:41',
                'updated_at' => '2017-12-20 16:57:41'
            ]
        ]);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGetAttributeEndpoint()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/editor/dm/attribute');

        $response->assertStatus(200);
        $response->assertJsonCount(15);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'thesaurus_url',
                'datatype',
                'text',
                'thesaurus_root_url',
                'parent_id',
                'created_at',
                'updated_at',
                'recursive',
                'root_attribute_id',
                'columns'
            ]
        ]);
        $response->assertJsonFragment([
            'id' => 5,
            'thesaurus_url' => 'https://spacialist.escience.uni-tuebingen.de/<user-project>/verzierung#20171220105421',
            'datatype' => 'table',
            'text' => null,
            'thesaurus_root_url' => null,
            'parent_id' => null,
            'created_at' => '2017-12-20 10:56:32',
            'updated_at' => '2017-12-20 10:56:32',
            'recursive' => true,
            'root_attribute_id' => null,
            'columns' => []
        ]);

        $content = json_decode($response->getContent());
        $cols = $content[3]->columns;
        $this->assertEquals(6, $cols[0]->id);
        $this->assertEquals(7, $cols[1]->id);
        $this->assertEquals(8, $cols[2]->id);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGetAttributeTypesEndpoint()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/editor/dm/attribute_types');

        $response->assertStatus(200);
        $response->assertJsonCount(17);
        $response->assertJsonStructure([
            '*' => [
                'datatype'
            ]
        ]);
        $response->assertExactJson([
            [
                'datatype' => 'string'
            ],
            [
                'datatype' => 'stringf'
            ],
            [
                'datatype' => 'double'
            ],
            [
                'datatype' => 'integer'
            ],
            [
                'datatype' => 'boolean'
            ],
            [
                'datatype' => 'string-sc'
            ],
            [
                'datatype' => 'string-mc'
            ],
            [
                'datatype' => 'epoch'
            ],
            [
                'datatype' => 'date'
            ],
            [
                'datatype' => 'dimension'
            ],
            [
                'datatype' => 'list'
            ],
            [
                'datatype' => 'geography'
            ],
            [
                'datatype' => 'percentage'
            ],
            [
                'datatype' => 'entity'
            ],
            [
                'datatype' => 'table'
            ],
            [
                'datatype' => 'sql'
            ],
            [
                'datatype' => 'serial'
            ]
        ]);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGetEntityTypeEndpoint()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/editor/entity_type/4');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'thesaurus_url',
            'is_root',
            'created_at',
            'updated_at',
            'sub_entity_types'
        ]);
        $response->assertJsonFragment([
            'id' => 4,
            'thesaurus_url' => 'https://spacialist.escience.uni-tuebingen.de/<user-project>/befund#20171220094916',
            'is_root' => false,
            'created_at' => '2017-12-20 10:03:15',
            'updated_at' => '2017-12-20 10:03:15'
        ]);

        $content = json_decode($response->getContent());
        $subs = $content->sub_entity_types;
        $this->assertEquals(3, $subs[0]->id);
        $this->assertEquals(4, $subs[1]->id);
        $this->assertEquals(5, $subs[2]->id);
        $this->assertEquals(6, $subs[3]->id);
        $this->assertEquals(7, $subs[4]->id);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGetEntityTypeAttributesEndpoint()
    {
        // Get an attribute (attribute_id=14) of the entity type to be tested (4);
        $ea = EntityAttribute::find(9);
        $ea->depends_on = '{"17": {"value": "placeholder", "operator": "=", "dependant": "14"}}';
        $ea->save();

        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/editor/entity_type/4/attribute');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
        $response->assertJsonStructure([
            'attributes' => [
                [
                    'id',
                    'entity_type_id',
                    'attribute_id',
                    'position',
                    'created_at',
                    'updated_at'
                ]
            ],
            'selections' => [
                '*' => [
                    [
                        'broader_id',
                        'narrower_id',
                        'id',
                        'concept_url',
                        'concept_scheme',
                        'is_top_concept',
                        'lasteditor',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ],
            'dependencies' => [
                '*' => [
                    [
                        'value',
                        'operator',
                        'dependant'
                    ]
                ]
            ]
        ]);

        $content = json_decode($response->getContent());
        $sels = $content->selections->{17};
        $deps = $content->dependencies->{17};
        $this->assertEquals(59, $sels[0]->id);
        $this->assertEquals(60, $sels[1]->id);
        $this->assertEquals(61, $sels[2]->id);
        $this->assertEquals(14, $deps[0]->dependant);
        $this->assertEquals('=', $deps[0]->operator);
        $this->assertEquals('placeholder', $deps[0]->value);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGetGeometryTypesEndpoint()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/editor/dm/geometry');

        $response->assertStatus(200);
        $response->assertJsonCount(6);
        $response->assertExactJson([
            'Point',
            'LineString',
            'Polygon',
            'MultiPoint',
            'MultiLineString',
            'MultiPolygon'
        ]);
    }
}
