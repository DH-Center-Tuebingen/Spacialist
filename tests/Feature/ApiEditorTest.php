<?php

namespace Tests\Feature;

use Tests\TestCase;

use App\Attribute;
use App\AvailableLayer;
use App\AttributeValue;
use App\Entity;
use App\EntityAttribute;
use App\EntityType;
use App\ThConcept;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\ResponseTester;
use Tests\Permission;

class ApiEditorTest extends TestCase
{
    // Testing GET requests

    /**
     * @testdox GET /api/v1/editor/dm/entity_type/occurrence_count/{id}  -  Get number of occurrences of an entity type (id=5)
     */
    public function testEntityOccurCountEndpoint()
    {
        $response = $this->userRequest()
            ->get('/api/v1/editor/dm/entity_type/occurrence_count/5');

        $this->assertStatus($response, 200);
        $response->assertSimilarJson([3]);
    }

    /**
     * @testdox GET /api/v1/editor/dm/attribute/occurrence_count/{id}  -  Get number of occurrences of an attribute (id=9)
     */
    public function testAttributeOccurCountEndpoint()
    {
        $response = $this->userRequest()
            ->get('/api/v1/editor/dm/attribute/occurrence_count/9');

        $this->assertStatus($response, 200);
        $response->assertSimilarJson([4]);
    }

    /**
     * @testdox GET /api/v1/editor/dm/attribute/occurrence_count/{attribute_id}/{entity_id}  -  Get number of occurrences of an attribute (id=9) on an entity (id=5)
     */
    public function testAttributeOfEntityTypeOccurCountEndpoint()
    {
        $response = $this->userRequest()
            ->get('/api/v1/editor/dm/attribute/occurrence_count/9/5');

        $this->assertStatus($response, 200);
        $response->assertSimilarJson([3]);
    }

    /**
     * @testdox GET /api/v1/editor/dm/entity_type/top  -  Get top-level entities
     */
    public function testTopEntityTypeCountEndpoint()
    {
        $response = $this->userRequest()
            ->get('/api/v1/editor/dm/entity_type/top');

        $this->assertStatus($response, 200);
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
        $response->assertSimilarJson([
            [
                'id' => 3,
                'thesaurus_url' => 'https://spacialist.escience.uni-tuebingen.de/<user-project>/fundstelle#20171220094911',
                'is_root' => true,
                'created_at' => '2017-12-20T10:03:06.000000Z',
                'updated_at' => '2017-12-20T10:03:06.000000Z',
                'color' => '#FF0000',
            ],
            [
                'id' => 7,
                'thesaurus_url' => 'https://spacialist.escience.uni-tuebingen.de/<user-project>/lagerstatte#20171220165727',
                'is_root' => true,
                'created_at' => '2017-12-20T16:57:41.000000Z',
                'updated_at' => '2017-12-20T16:57:41.000000Z',
                'color' => '#0000FF',
            ]
        ]);
    }

    /**
     * @ GET /api/v1/editor/dm/attribute - Get all attributes
    */
    public function testGetAttributeEndpoint()
    {
        $response = $this->userRequest()
            ->get('/api/v1/editor/dm/attribute');


        $this->assertStatus($response, 200);
        $response->assertJsonCount(21, 'attributes');
        $response->assertJsonStructure([
            'attributes' => [
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
                    'is_system',
                    'multiple',
                    'restrictions',
                    'metadata',
                    'entity_types_count',
                ]
            ]
        ]);
        $response->assertJson(fn(AssertableJson $json) =>
            $json
                ->has('attributes')
                ->has('attributes.1', fn($json) =>
                    $json
                        ->where('id', 2)
                        ->where('thesaurus_url', 'https://spacialist.escience.uni-tuebingen.de/<user-project>/erhaltung#20171220100437')
                        ->where( 'datatype', 'percentage')
                        ->where('thesaurus_root_url', NULL)
                        ->where('created_at', '2017-12-20T10:07:45.000000Z')
                        ->where('updated_at', '2017-12-20T10:07:45.000000Z')
                        ->where('parent_id', NULL)
                        ->where('text', NULL)
                        ->where('recursive', true)
                        ->where('root_attribute_id', NULL)
                        ->where('is_system', false)
                        ->where('multiple', false)
                        ->where('restrictions', NULL)
                        ->where('metadata', NULL)
                        ->where('entity_types_count', 1)
                )
                // This was originally an array keyed by index. But the backend changed
                // to use the id as key. Idk if this is a good idea. [SO]
                ->has('attributes.4.columns.6', fn($json) =>
                    $json
                        ->where('id', 6)
                        ->where('thesaurus_url',  'https://spacialist.escience.uni-tuebingen.de/<user-project>/gefassposition#20171220105434')
                        ->where('datatype',  'string-sc')
                        ->where('thesaurus_root_url',  'https://spacialist.escience.uni-tuebingen.de/<user-project>/gefassposition#20171220105434')
                        ->where('created_at',  '2017-12-20T10:56:32.000000Z')
                        ->where('updated_at',  '2017-12-20T10:56:32.000000Z')
                        ->where('parent_id',  5)
                        ->where('text',  NULL)
                        ->where('recursive',  true)
                        ->where('root_attribute_id', NULL)
                        ->where('is_system', false)
                        ->where('multiple', false)
                        ->where('restrictions', NULL)
                        ->where('metadata', NULL)
                )
                ->has('attributes.4.columns.7', fn($json) =>
                    $json
                        ->where('id', 7)
                        ->where('thesaurus_url', 'https://spacialist.escience.uni-tuebingen.de/<user-project>/verzierungselement#20171220105440')
                        ->where('datatype', 'string-sc')
                        ->where('thesaurus_root_url', 'https://spacialist.escience.uni-tuebingen.de/<user-project>/verzierungselement#20171220105440')
                        ->where('created_at', '2017-12-20T10:56:32.000000Z')
                        ->where('updated_at', '2017-12-20T10:56:32.000000Z')
                        ->where('parent_id', 5)
                        ->where('text', NULL)
                        ->where('recursive', true)
                        ->where('root_attribute_id', NULL)
                        ->where('is_system', false)
                        ->where('multiple', false)
                        ->where('restrictions', NULL)
                        ->where('metadata', NULL)
                )
                ->has('attributes.4.columns.8', fn($json) =>
                    $json
                        ->where('id', 8)
                        ->where('thesaurus_url', 'https://spacialist.escience.uni-tuebingen.de/<user-project>/notizen#20171220105603')
                        ->where('datatype', 'string')
                        ->where('thesaurus_root_url', NULL)
                        ->where('created_at', '2017-12-20T10:56:32.000000Z')
                        ->where('updated_at', '2017-12-20T10:56:32.000000Z')
                        ->where('parent_id', 5)
                        ->where('text', NULL)
                        ->where('recursive', true)
                        ->where('root_attribute_id', NULL)
                        ->where('is_system', false)
                        ->where('multiple', false)
                        ->where('restrictions', NULL)
                        ->where('metadata', NULL)
                    )
                ->has('selections')
        );
    }

    /**
     * @testdox GET /api/v1/editor/dm/attribute_types - Get all attribute types
     */
    public function testGetAttributeTypesEndpoint()
    {
        $response = $this->userRequest()
            ->get('/api/v1/editor/dm/attribute_types');

        $attributeTypes = [
            "boolean"       => true,
            "date"          => true,
            "daterange"     => true,
            "dimension"     => false,
            "double"        => true,
            "string-mc"     => true,
            "string-sc"     => true,
            "entity"        => true,
            "entity-mc"     => true,
            "epoch"         => false,
            "geography"     => true,
            "iconclass"     => true,
            "integer"       => true,
            "list"          => false,
            "percentage"    => false,
            "richtext"      => false,
            "rism"          => true,
            "serial"        => false,
            "sql"           => false,
            "string"        => true,
            "stringf"       => false,
            "table"         => false,
            "timeperiod"    => true,
            "userlist"      => true,
            "url"           => true,
            "si-unit"       => true,
        ];

        $this->assertStatus($response, 200);
        $response->assertJsonCount(count($attributeTypes));
        $response->assertJsonStructure([
            '*' => [
                'datatype'
            ]
        ]);
        $resultArray = [];
        foreach($attributeTypes as $datatype => $in_table) {
            $resultArray[] = ['datatype' => $datatype, 'in_table' => $in_table];
        }

        $response->assertSimilarJson($resultArray);
    }

    /**
     * @testdox GET /api/v1/editor/entity_type/{id} - Get enety type (id=3) including sub entity types
     */
    public function testGetEntityTypeEndpoint()
    {
        $response = $this->userRequest()
            ->get('/api/v1/editor/entity_type/3');

        $this->assertStatus($response, 200);
        $response->assertJsonStructure([
            'id',
            'thesaurus_url',
            'is_root',
            'created_at',
            'updated_at',
            'sub_entity_types'
        ]);
        $response->assertJsonFragment([
            'id' => 3,
            'thesaurus_url' => 'https://spacialist.escience.uni-tuebingen.de/<user-project>/fundstelle#20171220094911',
            'is_root' => true,
            'created_at' => '2017-12-20T10:03:06.000000Z',
            'updated_at' => '2017-12-20T10:03:06.000000Z'
        ]);

        $content = json_decode($response->getContent());
        $subs = $content->sub_entity_types;
        $this->assertEquals(3, $subs[0]->id);
        $this->assertEquals(4, $subs[1]->id);
        $this->assertEquals(5, $subs[2]->id);
        $this->assertEquals(6, $subs[3]->id);
        $this->assertEquals(7, $subs[4]->id);
    }

    // TODO: This seems to be deprecated, the endpoint does not exist anymore.
    //
    // /**
    //  * @testdox GET /api/v1/editor/entity_type/{id}/attribute  -  Test getting attributes of an entity type (id=4) including dropdown menu selections and dependencies.
    //  */
    // public function testGetEntityTypeAttributesEndpoint()
    // {
    //     // Get an attribute (attribute_id=14) of the entity type to be tested (4);
    //     $ea = EntityAttribute::find(9);
    //     $ea->depends_on = '{"17": {"value": "placeholder", "operator": "=", "dependant": "14"}}';
    //     $ea->save();

    //     $response = $this->userRequest()
    //         ->get('/api/v1/editor/entity_type/4/attribute');

    //     $this->assertStatus($response, 200);
    //     $response->assertJsonCount(3);
    //     $response->assertJsonStructure([
    //         'attributes' => [
    //             [
    //                 'id',
    //                 'entity_type_id',
    //                 'attribute_id',
    //                 'position',
    //                 'created_at',
    //                 'updated_at'
    //             ]
    //         ],
    //         'selections' => [
    //             '*' => [
    //                 [
    //                     'broader_id',
    //                     'narrower_id',
    //                     'id',
    //                     'concept_url',
    //                     'concept_scheme',
    //                     'is_top_concept',
    //                     'user_id',
    //                     'created_at',
    //                     'updated_at'
    //                 ]
    //             ]
    //         ],
    //         'dependencies' => [
    //             '*' => [
    //                 [
    //                     'value',
    //                     'operator',
    //                     'dependant'
    //                 ]
    //             ]
    //         ]
    //     ]);

    //     $content = json_decode($response->getContent());
    //     $sels = $content->selections->{17};
    //     $deps = $content->dependencies->{17};
    //     $this->assertEquals(59, $sels[0]->id);
    //     $this->assertEquals(60, $sels[1]->id);
    //     $this->assertEquals(61, $sels[2]->id);
    //     $this->assertEquals(14, $deps[0]->dependant);
    //     $this->assertEquals('=', $deps[0]->operator);
    //     $this->assertEquals('placeholder', $deps[0]->value);
    // }

    // TODO:: Deprecated (?)
    //
    // /**
    //  * Test getting entries of a dropdown attribute (id=14).
    //  *
    //  * @return void
    //  */
    // public function testGetAttributeSelectionEndpoint()
    // {
    //     $response = $this->userRequest()
    //         ->get('/api/v1/editor/attribute/14/selection');

    //     $this->assertStatus($response, 200);
    //     $response->assertJsonCount(3);
    //     $response->assertJson([
    //         ['id' => 52],
    //         ['id' => 53],
    //         ['id' => 54],
    //     ]);
    // }

    // Testing POST requests

    /**
     * @testdox POST /api/v1/editor/dm/entity_type  -  Add a new entity type
     */
    public function testAddEntityTypeEndpoint()
    {
        $concept = ThConcept::first();

        $response = $this->userRequest()
            ->post('/api/v1/editor/dm/entity_type', [
                'concept_url' => $concept->concept_url,
                'is_root' => true,
                'geometry_type' => 'Any'
            ]);

        $entityType = EntityType::latest()->first();

        $this->assertStatus($response, 201);
        $response->assertJsonStructure([
            'id',
            'thesaurus_url',
            'is_root',
            'created_at',
            'updated_at',
            'color',
        ]);

        $response->assertJson(fn(AssertableJson $json) =>
            $json
                ->has('id')
                ->has('thesaurus_url')
                ->has('is_root')
                ->has('created_at')
                ->has('updated_at')
                ->has('color')
                ->where('id', $entityType->id)
                ->where('thesaurus_url', $concept->concept_url)
                ->where('is_root', true)
                ->where('created_at', $entityType->created_at->toJSON())
                ->where('updated_at', $entityType->updated_at->toJSON())
                ->where('color', $entityType->color)
        );

        // DISCUSS: Is this relevant anymore?
        // $entityTypeLayer = AvailableLayer::latest()->first();
        // $this->assertEquals($entityType->id, $entityTypeLayer->entity_type_id);

        // $layMax = AvailableLayer::where('is_overlay', true)->max('position');
        // $this->assertEquals($layMax+1, $entityTypeLayer->position);
    }

    // TODO check if necessary to replace old set relation logic with new logic in EditorController::patchEntityType
    // /**
    // * @testdox POST /api/v1/editor/dm/{id}/relation  -  Modify entity type relations
    // */
    // public function testModifyingEntityTypeRelation() {
    //     $id = 4;
    //     $response = $this->userRequest()
    //         ->post("/api/v1/editor/dm/$id/relation", [
    //             'is_root' => false,
    //             'sub_entity_types' => [
    //                 3, 6, 7
    //             ]
    //         ]);
    // }

    // /**
    // * @testdox POST /api/v1/editor/dm/{id}/relation  -  Modify entity type relations
    // */
    // public function testModifyingEntityTypeRelation() {
    //     $id = 4;
    //     $response = $this->userRequest()
    //         ->post("/api/v1/editor/dm/$id/relation", [
    //             'is_root' => false,
    //             'sub_entity_types' => [
    //                 3, 6, 7
    //             ]
    //         ]);

    //     $this->assertStatus($response, 204);

    //     $entityType = EntityType::find($id)->load('sub_entity_types');
    //     $this->assertTrue(!$entityType->is_root);
    //     $this->assertArraySubset([
    //         ['id' => 3],
    //         ['id' => 6],
    //         ['id' => 7]
    //     ], $entityType->sub_entity_types->toArray());
    // }

    /**
     *  @testdox POST /api/v1/editor/dm/attribute  -  Test adding a new entity type and modifying it's relations afterwards.
     */
    public function testAddAttributeEndpoint()
    {
        $concept = ThConcept::first();
        $response = $this->userRequest()
            ->post('/api/v1/editor/dm/attribute', [
                'label_id' => $concept->id,
                'datatype' => 'string-sc',
                'root_id' => $concept->id,
                'recursive' => false
            ]);

        $attribute = Attribute::orderBy('id', 'desc')->first();
        $this->assertStatus($response, 201);
        $response->assertJsonStructure([
            'attribute' => [
                'id',
                'thesaurus_url',
                'datatype',
                'text',
                'thesaurus_root_url',
                'parent_id',
                'created_at',
                'updated_at',
                'recursive',
                'root_attribute_id'
            ],
            'selection'
        ]);

        $response->assertJson(fn(AssertableJson $json) =>
            $json
                ->has('attribute', fn($json) =>
                    $json
                        ->has("id")
                        ->has('thesaurus_url')
                        ->has('datatype')
                        ->has('text')
                        ->has('thesaurus_root_url')
                        ->has('parent_id')
                        ->has('created_at')
                        ->has('updated_at')
                        ->has('recursive')
                        ->has('root_attribute_id')
                        ->has('is_system')
                        ->has('multiple')
                        ->has('restrictions')
                        ->has('metadata')
                        ->has('columns')
                        ->where('thesaurus_url', $concept->concept_url)
                        ->where('datatype', 'string-sc')
                        ->where('text', null)
                        ->where('thesaurus_root_url', $concept->concept_url)
                        ->where('parent_id', null)
                        ->where('created_at', $attribute->created_at->toJSON())
                        ->where('updated_at', $attribute->updated_at->toJSON())
                        ->where('recursive', false)
                        ->where('root_attribute_id', null)
                        ->where('is_system', false)
                        ->where('multiple', false)
                        ->where('restrictions', null)
                        ->where('metadata', null)
                        ->where('columns', [])
                )
                ->has('selection.0', fn($json) =>
                    $json
                        ->has('id')
                        ->has('concept_scheme')
                        ->has('concept_url')
                        ->has('created_at')
                        ->has('updated_at')
                        ->has('is_top_concept')
                        ->has('narrower_id')
                        ->has('broader_id')
                        ->has('user_id')
                        ->where('id', 48)
                )
                ->has('selection.1', fn($json) =>
                    $json
                        ->has('id')
                        ->has('concept_scheme')
                        ->has('concept_url')
                        ->has('created_at')
                        ->has('updated_at')
                        ->has('is_top_concept')
                        ->has('narrower_id')
                        ->has('broader_id')
                        ->has('user_id')
                        ->where('id', 62)
                )
        );
    }

    /**
     *  @testdox POST /api/v1/editor/dm/entity_type/{id}/attribute  -  Adding attributes to an entity type (id=3).
     */
    public function testAddAttributeToEntityTypeEndpoint()
    {
        $response = $this->userRequest()
            ->post('/api/v1/editor/dm/entity_type/3/attribute', [
                'attribute_id' => 2,
                'position' => 1
            ]);

        $this->assertStatus($response, 201);
        $response->assertJsonStructure([
            'id',
            'entity_type_id',
            'attribute_id',
            'position',
            'depends_on',
            'created_at',
            'updated_at',
            'attribute' => [
                'id',
                'thesaurus_url',
                'datatype',
                'text',
                'thesaurus_root_url',
                'parent_id',
                'created_at',
                'updated_at',
                'recursive',
                'root_attribute_id'
            ]
        ]);
        $response->assertJson([
            'entity_type_id' => 3,
            'attribute_id' => 2,
            'position' => 1,
            'depends_on' => null,
            'attribute' => [
                'id' => 2,
                'thesaurus_url' => 'https://spacialist.escience.uni-tuebingen.de/<user-project>/erhaltung#20171220100437',
                'datatype' => 'percentage',
                'text' => null,
                'thesaurus_root_url' => null,
                'parent_id' => null,
                'recursive' => true,
                'root_attribute_id' => null
            ]
        ]);

        $response = $this->userRequest()
            ->post('/api/v1/editor/dm/entity_type/3/attribute', [
                'attribute_id' => 3
            ]);
        $this->assertStatus($response, 201);
        $response->assertJsonStructure([
            'id',
            'entity_type_id',
            'attribute_id',
            'position',
            'depends_on',
            'created_at',
            'updated_at',
            'attribute' => [
                'id',
                'thesaurus_url',
                'datatype',
                'text',
                'thesaurus_root_url',
                'parent_id',
                'created_at',
                'updated_at',
                'recursive',
                'root_attribute_id'
            ]
        ]);
        $response->assertJson([
            'entity_type_id' => 3,
            'attribute_id' => 3,
            'position' => 9,
            'depends_on' => null,
            'attribute' => [
                'id' => 3,
                'thesaurus_url' => 'https://spacialist.escience.uni-tuebingen.de/<user-project>/farbe#20171220100506',
                'datatype' => 'string-mc',
                'text' => null,
                'thesaurus_root_url' => null,
                'parent_id' => null,
                'recursive' => true,
                'root_attribute_id' => null
            ]
        ]);
    }

    /**
     *  @testdox POST /api/v1/editor/dm/entity_type/{id}/duplicate  -  Duplicating an entity type (id=3).
     */
    public function testDuplicateEntityTypeEndpoint()
    {
        $entityType = EntityType::find(3)->load('sub_entity_types');
        $layMax = AvailableLayer::where('is_overlay', true)->max('position');
        $response = $this->userRequest()
            ->post('/api/v1/editor/dm/entity_type/3/duplicate');

        $newEntityType = EntityType::latest()->first();

        $this->assertStatus($response, 200);
        $response->assertJsonStructure([
            'id',
            'thesaurus_url',
            'is_root',
            'created_at',
            'updated_at',
            'sub_entity_types'
        ]);
        $response->assertJson([
            'id' => $newEntityType->id,
            'thesaurus_url' => $entityType->thesaurus_url,
            'is_root' => $entityType->is_root,
            'created_at' => $newEntityType->created_at->toJSON(),
            'updated_at' => $newEntityType->updated_at->toJSON()
        ]);

        $content = json_decode($response->getContent());
        $etSubTypeIds = $entityType->sub_entity_types->pluck('id');
        $this->assertEquals(count($etSubTypeIds), count($content->sub_entity_types));
    }

    /**
     *  @testdox PATCH /api/v1/editor/dm/entity_type/{id}  -  Editing an entity type (id=3).
     */
    public function testEditEntityTypeEndpoint()
    {
        $entityType = EntityType::find(3);
        $this->assertEquals('https://spacialist.escience.uni-tuebingen.de/<user-project>/fundstelle#20171220094911', $entityType->thesaurus_url);

        $response = $this->userRequest()
            ->patch('/api/v1/editor/dm/entity_type/3', [
                'data' => [
                    'thesaurus_url' => 'https://spacialist.escience.uni-tuebingen.de/<user-project>/erhaltung#20171220100437'
                ]
            ]);

        $this->assertStatus($response, 200);

        $entityType = EntityType::find(3);
        $this->assertEquals('https://spacialist.escience.uni-tuebingen.de/<user-project>/erhaltung#20171220100437', $entityType->thesaurus_url);
    }

    /**
     *  @testdox PATCH /api/v1/editor/dm/entity_type/{id}/attribute/{aid}/position  -   Test reordering attributes of an entity type (id=4).
     */
    public function testRoorderEntityTypeAttributesEndpoint()
    {
        $entityType = EntityType::find(3)->load('attributes');
        foreach($entityType->attributes as $a) {
            if($a->attribute_id == 14) {
                $this->assertEquals(1, $a->pivot->position);
            } else if($a->attribute_id == 16) {
                $this->assertEquals(2, $a->pivot->position);
            } else if($a->attribute_id == 17) {
                $this->assertEquals(3, $a->pivot->position);
            } else if($a->attribute_id == 10) {
                $this->assertEquals(4, $a->pivot->position);
            } else if($a->attribute_id == 13) {
                $this->assertEquals(5, $a->pivot->position);
            }
        }

        $response = $this->userRequest()
            ->patch('/api/v1/editor/dm/entity_type/4/attribute/14/position', [
                'position' => 4
            ]);

        $this->assertStatus($response, 204);

        $entityType = EntityType::find(3)->load('attributes');
        foreach($entityType->attributes as $a) {
            if($a->attribute_id == 14) {
                $this->assertEquals(4, $a->pivot->position);
            } else if($a->attribute_id == 16) {
                $this->assertEquals(1, $a->pivot->position);
            } else if($a->attribute_id == 17) {
                $this->assertEquals(2, $a->pivot->position);
            } else if($a->attribute_id == 10) {
                $this->assertEquals(3, $a->pivot->position);
            } else if($a->attribute_id == 13) {
                $this->assertEquals(5, $a->pivot->position);
            }
        }

        // Testing again with higher position
        $response = $this->userRequest()
            ->patch('/api/v1/editor/dm/entity_type/4/attribute/13/position', [
                'position' => 1
            ]);

        $this->assertStatus($response, 204);

        $entityType = EntityType::find(3)->load('attributes');
        foreach($entityType->attributes as $a) {
            if($a->attribute_id == 14) {
                $this->assertEquals(5, $a->pivot->position);
            } else if($a->attribute_id == 16) {
                $this->assertEquals(2, $a->pivot->position);
            } else if($a->attribute_id == 17) {
                $this->assertEquals(3, $a->pivot->position);
            } else if($a->attribute_id == 10) {
                $this->assertEquals(4, $a->pivot->position);
            } else if($a->attribute_id == 13) {
                $this->assertEquals(1, $a->pivot->position);
            }
        }

        // Testing again with same position (nothing should happen)
        $response = $this->userRequest()
            ->patch('/api/v1/editor/dm/entity_type/4/attribute/14/position', [
                'position' => 4
            ]);

        $this->assertStatus($response, 204);

        $entityType = EntityType::find(3)->load('attributes');
        foreach($entityType->attributes as $a) {
            if($a->attribute_id == 14) {
                $this->assertEquals(4, $a->pivot->position);
            } else if($a->attribute_id == 16) {
                $this->assertEquals(1, $a->pivot->position);
            } else if($a->attribute_id == 17) {
                $this->assertEquals(2, $a->pivot->position);
            } else if($a->attribute_id == 10) {
                $this->assertEquals(3, $a->pivot->position);
            } else if($a->attribute_id == 13) {
                $this->assertEquals(5, $a->pivot->position);
            }
        }
    }

    /**
     *  @testdox PATCH /api/v1/editor/dm/entity_type/{id}/attribute/{aid}/dependency  -   Test adding dependency to an attribute of an entity type (id=4).
     */
    public function testAddDependencyToEntiyTypeAttributeEndpoint()
    {
        $response = $this->userRequest()
            ->patch('/api/v1/editor/dm/entity_type/4/attribute/14/dependency', [
                'data' => [
                    'or' => false,
                    'groups' => [
                        [
                            'or' => true,
                            'rules' => [
                                [
                                    'attribute' => 13,
                                    'operator' => '=',
                                    'value' => 'Test Value'
                                ],
                            ],
                        ]
                    ],
                ],
            ]);

        $this->assertStatus($response, 200);

        $entityAttribute = EntityAttribute::for(4, 14);
        $this->assertArrayHasKey('depends_on', $entityAttribute);
        $this->assertEquals([
            'or' => false,
            'groups' => [
                [
                    'or' => true,
                    'rules' => [
                        [
                            'operator' => '=',
                            'value' => 'Test Value',
                            'on' => 13,
                        ],
                    ],
                ]
            ],
        ], $entityAttribute->depends_on);
    }

    /**
     *  @testdox PATCH /api/v1/editor/dm/entity_type/{id}/attribute/{aid}/dependency  -   Test adding dependency without data to an attribute of an entity type (id=4).
     */
    public function testAddEmptyDependencyToEntiyTypeAttributeEndpoint()
    {
        $response = $this->userRequest()
            ->patch('/api/v1/editor/dm/entity_type/4/attribute/14/dependency', [
                'data' => [
                    'or' => false,
                    'groups' => [],
                ],
            ]);

        $this->assertStatus($response, 200);

        $entityAttribute = EntityAttribute::for(4, 14);
        $this->assertNull($entityAttribute->depends_on);
    }

    /**
     *  @testdox DELETE /api/v1/editor/dm/entity_type/{id}  -   Test deleting an entity type (id=4).
     */
    public function testDeleteEntityTypeEndpoint()
    {
        $etCnt = EntityType::count();
        $this->assertEquals(5, $etCnt);
        $eaCnt = EntityAttribute::count();
        $this->assertEquals(29, $eaCnt);
        $eCnt = Entity::count();
        $this->assertEquals(8, $eCnt);
        $avCnt = AttributeValue::count();
        $this->assertEquals(31, $avCnt);

        $response = $this->userRequest()
            ->delete('/api/v1/editor/dm/entity_type/4');

        $this->assertStatus($response, 204);

        $etCnt = EntityType::count();
        $this->assertEquals(4, $etCnt);
        $eaCnt = EntityAttribute::count();
        $this->assertEquals(23, $eaCnt);
        $eCnt = Entity::count();
        $this->assertEquals(4, $eCnt);
        $avCnt = AttributeValue::count();
        $this->assertEquals(11, $avCnt);
    }

     /**
      * @testdox DELETE /api/v1/editor/dm/attribute/{id}  -  Test deleting an attribute (id=12).
      */
    public function testDeleteAttributeEndpoint()
    {
        $eaCnt = EntityAttribute::count();
        $this->assertEquals(29, $eaCnt);
        $avCnt = AttributeValue::count();
        $this->assertEquals(31, $avCnt);
        $aCnt = Attribute::count();
        $this->assertEquals(24, $aCnt);

        $response = $this->userRequest()
            ->delete('/api/v1/editor/dm/attribute/12');

        $this->assertStatus($response, 204);

        $eaCnt = EntityAttribute::count();
        $this->assertEquals(27, $eaCnt);
        $avCnt = AttributeValue::count();
        $this->assertEquals(27, $avCnt);
        $aCnt = Attribute::count();
        $this->assertEquals(23, $aCnt);
    }

     /**
      * @testdox DELETE /api/v1/editor/dm/entity_type/{id}/attribute/{aid}  -  Test deleting an attribute (id=11) from an entity type (id=5).
      */
    public function testDeleteAttributeFromEntityTypeEndpoint()
    {
        $eaCnt = EntityAttribute::count();
        $this->assertEquals(29, $eaCnt);
        $avCnt = AttributeValue::count();
        $this->assertEquals(31, $avCnt);
        $entityType = EntityType::find(5)->load('attributes');

        $oldPositions = [12, 9, 11, 2, 3, 5, 19, 4, 13];
        foreach($entityType->attributes as $entityType) {
            foreach($oldPositions as $index => $position) {
                if($entityType->id == $position) {
                    $this->assertEquals($index+1, $entityType->pivot->position);
                    break;
                }
            }
        }

        $entityAttribute = EntityAttribute::for(5, 11);
        $response = $this->userRequest()
            ->delete("/api/v1/editor/dm/entity_type/attribute/$entityAttribute->id");

        $this->assertStatus($response, 204);

        $eaCnt = EntityAttribute::count();
        $this->assertEquals(28, $eaCnt);
        $avCnt = AttributeValue::count();
        $this->assertEquals(28, $avCnt);
        $entityType = EntityType::find(5)->load('attributes');
        $newPositions = [12, 9, 2, 3, 5, 19, 4, 13];
        foreach($entityType->attributes as $entityType) {
            foreach($newPositions as $index => $position) {
                if($entityType->id == $position) {
                    $this->assertEquals($index+1, $entityType->pivot->position);
                    break;
                }
            }
        }
    }

    /**
     * @dataProvider permissionsProvider
     */
    public function testWithoutPermission($permission) {
        (new ResponseTester($this))->testMissingPermission($permission);
    }

    /**
     * @dataProvider exceptionsProvider
     */
    public function testExceptions($permission) {
        (new ResponseTester($this))->testExceptions($permission);
    }

    public static function permissionsProvider() {
        return [
            'permission to get entity type'                        => Permission::for("get", "/api/v1/editor/entity_type/1",           "You do not have the permission to get an entity type's data"),
            'permission to view entity data on top entity types'   => Permission::for("get", "/api/v1/editor/dm/entity_type/top",      "You do not have the permission to view entity data"),
            'permission to view entity data'                       => Permission::for("get", "/api/v1/editor/dm/attribute",           "You do not have the permission to view entity data"),
            'permission to create entity type'                     => Permission::for("post", "/api/v1/editor/dm/entity_type",        "You do not have the permission to create a new entity type"),
            // TODO check if necessary to replace old set relation logic with new logic in EditorController::patchEntityType
            // 'permission to modify entity relations'                => Permission::for("post", "/api/v1/editor/dm/1/relation",     "You do not have the permission to modify entity relations"),
            'permission to view entity data'                       => Permission::for("get", "/api/v1/editor/entity_type/1/attribute", "You do not have the permission to view entity data"),
            'permission to view entity data on top entity types'   => Permission::for("get", "/api/v1/editor/dm/entity_type/top",      "You do not have the permission to view entity data"),
            'permission to view entity data'                       => Permission::for("get", "/api/v1/editor/dm/attribute",           "You do not have the permission to view entity data"),
            'permission to create entity type'                     => Permission::for("post", "/api/v1/editor/dm/entity_type",        "You do not have the permission to create a new entity type"),
            'permission to add attributes'                         => Permission::for("post", "/api/v1/editor/dm/attribute",          "You do not have the permission to add attributes",[
                    'label_id' => 1,
                    'datatype' => 'string-sc',
                    'root_id' => 1,
                    'recursive' => false
            ]),
            'permission to add attributes to an entity type'    => Permission::for("post", "/api/v1/editor/dm/entity_type/1/attribute", "You do not have the permission to add attributes to an entity type"),
            'permission to duplicate an entity type'            => Permission::for("post", "/api/v1/editor/dm/entity_type/1/duplicate", "You do not have the permission to duplicate an entity type"),
            'permission to modify entity-type'                  => Permission::for("patch", "/api/v1/editor/dm/entity_type/1", "You do not have the permission to modify entity-type",[
                'data' => [
                    'thesaurus_url' => 'https://spacialist.escience.uni-tuebingen.de/<user-project>/fundstelle#20171220094911'
                ]
            ]),
            'permission to reorder attributes'                     => Permission::for("patch", "/api/v1/editor/dm/entity_type/1/attribute/1/position", "You do not have the permission to reorder attributes"),
            'permission to add/modify attribute dependencies'      => Permission::for("patch", "/api/v1/editor/dm/entity_type/1/attribute/1/dependency", "You do not have the permission to add/modify attribute dependencies", [
                    'data' => [
                        'or' => false,
                        'groups' => [
                            [
                                'or' => true,
                                'rules' => [
                                    [
                                        'attribute' => 15,
                                        'operator' => '=',
                                        'value' => 'NoValue',
                                    ],
                                ],
                            ]
                        ],
                    ],
                ]),
            'permission to edit metadata of an attribute'                    => Permission::for("patch", "/api/v1/editor/dm/entity_type/attribute/1/metadata", "You do not have the permission to edit attribute names"),
            'permission to delete entity types'                    => Permission::for("delete", "/api/v1/editor/dm/entity_type/1", "You do not have the permission to delete entity types"),
            'permission to delete attributes'                      => Permission::for("delete", "/api/v1/editor/dm/attribute/1", "You do not have the permission to delete attributes"),
            'permission to remove attributes from entity types'    => Permission::for("delete", "/api/v1/editor/dm/entity_type/attribute/19", "You do not have the permission to remove attributes from entity types"),
        ];
    }

    public static function exceptionsProvider() {
        $entityDoesNotExist = "This entity-type does not exist";
        $entityAttributeNotFound = "Entity Attribute not found";
        $attributeDoesNotExist = "This attribute does not exist";

        return [
            'exception on get entity type'                         => Permission::for("get", "/api/v1/editor/entity_type/99", $entityDoesNotExist),
            'exception on view entity data'                        => Permission::for("post", "/api/v1/editor/dm/entity_type/99/attribute", $entityDoesNotExist,[
                'attribute_id' => 2,
                'position' => 1
            ]),
            // TODO check if necessary to replace old set relation logic with new logic in EditorController::patchEntityType
            // 'exception on modify entity relations'                 => Permission::for("post", "/api/v1/editor/dm/99/relation", $entityDoesNotExist),
            'exception on add attributes to an entity type'        => Permission::for("post", "/api/v1/editor/dm/entity_type/99/attribute", $entityDoesNotExist,[
                'attribute_id' => 2,
                'position' => 1
            ]),
            'exception on duplicate an entity type'                => Permission::for("post", "/api/v1/editor/dm/entity_type/99/duplicate", $entityDoesNotExist),
            'exception on modify entity-type'                      => Permission::for("patch", "/api/v1/editor/dm/entity_type/99", $entityDoesNotExist,[
                'data' => [
                    'thesaurus_url' => 'https://spacialist.escience.uni-tuebingen.de/<user-project>/fundstelle#20171220094911'
                ]
            ]),
            'exception on reorder attributes'                      => Permission::for("patch", "/api/v1/editor/dm/entity_type/1/attribute/99/position", $entityAttributeNotFound, [
                'position' => 1
            ]),
            'exception on add/modify attribute dependencies'       => Permission::for("patch", "/api/v1/editor/dm/entity_type/1/attribute/99/dependency", $entityAttributeNotFound, [
                    'data' => [
                        'or' => false,
                        'groups' => [
                            [
                                'or' => true,
                                'rules' => [
                                    [
                                        'attribute' => 15,
                                        'operator' => '=',
                                        'value' => 'NoValue',
                                    ],
                                ],
                            ]
                        ],
                    ]
                ]),
            'exception on add/modify attribute dependencies with wrong reference'       => Permission::for("patch", "/api/v1/editor/dm/entity_type/3/attribute/15/dependency", 'Entity attribute does not exist', [
                    'data' => [
                        'or' => false,
                        'groups' => [
                            [
                                'or' => true,
                                'rules' => [
                                    [
                                        'attribute' => 99,
                                        'operator' => '=',
                                        'value' => 'NoValue',
                                    ],
                                ],
                            ]
                        ],
                    ]
                ]),
            'exception on add/modify attribute dependencies with operator mismatch'       => Permission::for("patch", "/api/v1/editor/dm/entity_type/3/attribute/15/dependency", 'Operator mismatch', [
                    'data' => [
                        'or' => false,
                        'groups' => [
                            [
                                'or' => true,
                                'rules' => [
                                    [
                                        'attribute' => 15,
                                        'operator' => 'INVALID',
                                        'value' => 'NoValue',
                                    ],
                                ],
                            ]
                        ],
                    ]
                ]),
            'exception on delete entity types'                     => Permission::for("delete", "/api/v1/editor/dm/entity_type/99", $entityDoesNotExist),
            'exception on delete attributes'                       => Permission::for("delete", "/api/v1/editor/dm/attribute/99", $attributeDoesNotExist),
            'exception on remove attributes from entity types'     => Permission::for("delete", "/api/v1/editor/dm/entity_type/attribute/99", $entityAttributeNotFound),
        ];
    }
}
