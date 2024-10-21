<?php

namespace Tests\Feature;

use App\Bibliography;
use App\Entity;
use App\ThLanguage;
use App\User;
use Tests\TestCase;

class ApiSearchTest extends TestCase
{
    // Testing GET requests

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGlobalSearchEntityEndpoint()
    {
        $response = $this->userRequest()
            ->get('/api/v1/search?q=!e inv.');

        $response->assertJsonCount(3);
        $response->assertJsonStructure([
            [
                'searchable' => [
                    'id',
                    'name',
                    'entity_type_id',
                    'root_entity_id',
                    'rank',
                    'user_id',
                    'created_at',
                    'updated_at',
                    'parentIds',
                ],
                'title',
                'url',
                'type',
            ]
        ]);

        $response->assertJsonFragment([
            'searchable' => Entity::find(3)->toArray(),
            'title' => 'Inv. 1234',
            'type' => 'entities',
            'url' => null,
        ]);
        $response->assertJsonFragment([
            'searchable' => Entity::find(4)->toArray(),
            'title' => 'Inv. 124',
            'type' => 'entities',
            'url' => null,
        ]);
        $response->assertJsonFragment([
            'searchable' => Entity::find(5)->toArray(),
            'title' => 'Inv. 31',
            'type' => 'entities',
            'url' => null,
        ]);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGlobalSearchBibliographyEndpoint()
    {
        $fields = [
            'entry_type' => 'article',
            'citekey' => '',
            'title' => 'Testing API',
            'author' => 'API (Inv.) Tester',
            'note' => 'Test Inv. Match Entity Name',
            'user_id' => 1,
            'year' => 2009
        ];
        $fields['citekey'] = Bibliography::computeCitationKey($fields);
        $bibEntry = new Bibliography();
        foreach($fields as $k => $v) {
            $bibEntry->{$k} = $v;
        }
        $bibEntry->save();

        $response = $this->userRequest()
            ->get('/api/v1/search?q=!b iNv.');

        $response->assertJsonCount(1);
        $response->assertJsonStructure([
            [
                'searchable' => [
                    'id',
                    'entry_type',
                    'citekey',
                    'title',
                    'author',
                    'editor',
                    'journal',
                    'year',
                    'pages',
                    'volume',
                    'number',
                    'booktitle',
                    'publisher',
                    'address',
                    'misc',
                    'howpublished',
                    'annote',
                    'chapter',
                    'crossref',
                    'edition',
                    'institution',
                    'key',
                    'month',
                    'note',
                    'organization',
                    'school',
                    'series',
                    'user_id',
                    'created_at',
                    'updated_at',
                ],
                'title',
                'url',
                'type',
            ]
        ]);
        $response->assertJson([
            [
                'searchable' => Bibliography::find($bibEntry->id)->toArray(),
                'title' => 'Testing API',
                'type' => 'bibliography',
                'url' => null,
            ]
        ]);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGlobalSearchAllEndpoint()
    {
        $nowTs = time();
        $now = date('Y-m-d H:i:s', $nowTs);

        $fields = [
            'entry_type' => 'article',
            'citekey' => '',
            'title' => 'Testing API',
            'author' => 'API (Inv.) Tester',
            'note' => 'Test Inv. Match Entity Name',
            'user_id' => 1,
            'year' => 2009
        ];
        $fields['citekey'] = Bibliography::computeCitationKey($fields);
        $bibEntry = new Bibliography();
        foreach($fields as $k => $v) {
            $bibEntry->{$k} = $v;
        }
        $bibEntry->save();

        $response = $this->userRequest()
            ->get('/api/v1/search?q=inV.');

        $response->assertJsonCount(4);

        $response->assertJsonFragment([
            'title' => 'Inv. 1234',
            'type' => 'entities'
        ]);
        $response->assertJsonFragment([
            'title' => 'Inv. 124',
            'type' => 'entities'
        ]);
        $response->assertJsonFragment([
            'title' => 'Inv. 31',
            'type' => 'entities'
        ]);
        $response->assertJsonFragment([
            'title' => 'Testing API',
            'type' => 'bibliography',
            'searchable' => Bibliography::find($bibEntry->id)->toArray(),
        ]);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testEntitySearchEndpoint()
    {
        $response = $this->userRequest()
            ->get('/api/v1/search/entity?q=Inv.');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
        $response->assertJsonStructure([
            [
                'id',
                'name',
                'entity_type_id',
                'root_entity_id',
                'rank',
                'user_id',
                'created_at',
                'updated_at',
                'ancestors'
            ]
        ]);

        $response->assertJson([
            ['name' => 'Inv. 1234'],
            ['name' => 'Inv. 124'],
            ['name' => 'Inv. 31']
        ]);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testLabelSearchEndpoint()
    {
        $response = $this->userRequest()
            ->get('/api/v1/search/label?q=ind');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
        $response->assertJsonStructure([
            [
                'id',
                'concept_url',
                'concept_scheme',
                'is_top_concept',
                'user_id',
                'created_at',
                'updated_at',
                'labels'
            ]
        ]);
        $response->assertJson([
            [
                'id' => 3,
                'is_top_concept' => true,
                'labels' => [
                    [
                        'label' => 'Fundobjekt',
                        'language_id' => 1
                    ],
                    [
                        'label' => 'Find',
                        'language_id' => 2
                    ]
                ]
            ],
            [
                'id' => 41,
                'is_top_concept' => false,
                'labels' => [
                    [
                        'label' => 'Kammeindruck',
                        'language_id' => 1
                    ]
                ]
            ],
        ]);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testAttributeSearchEndpoint()
    {
        $response = $this->userRequest()
            ->get('/api/v1/search/attribute?q=ung');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonStructure([
            [
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
                'thesaurus_concept'
            ]
        ]);
        $response->assertJson([
            [
                'id' => 7,
                'thesaurus_url' => 'https://spacialist.escience.uni-tuebingen.de/<user-project>/verzierungselement#20171220105440',
                'datatype' => 'string-sc',
                'text' => null,
                'thesaurus_root_url' => 'https://spacialist.escience.uni-tuebingen.de/<user-project>/verzierungselement#20171220105440',
                'parent_id' => 5,
                'recursive' => TRUE,
                'root_attribute_id' => null,
                'thesaurus_concept' => [
                    'id' => 34
                ]
            ]
        ]);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSelectionSearchEndpoint()
    {
        $response = $this->userRequest()
            ->get('/api/v1/search/selection/13');

        $response->assertStatus(200);
        $response->assertJsonCount(5);
        $response->assertJsonStructure([
            [
                'id',
                'concept_url',
                'concept_scheme',
                'is_top_concept',
                'user_id',
                'broader_id',
                'narrower_id',
                'created_at',
                'updated_at'
            ]
        ]);
        $response->assertJson([
            ['id' => 15],
            ['id' => 16],
            ['id' => 17],
            ['id' => 43],
            ['id' => 47]
        ]);
    }

    // Testing exceptions and permissions

    /**
     *
     *
     * @return void
     */
    public function testPermissions()
    {
        User::first()->roles()->detach();

        $calls = [
            ['url' => '', 'error' => 'You do not have the permission to search global'],
            ['url' => '/entity', 'error' => 'You do not have the permission to search for entities'],
            ['url' => '/label', 'error' => 'You do not have the permission to search for concepts'],
            ['url' => '/selection/12', 'error' => 'You do not have the permission to search for concepts'],
        ];

        $response = null;
        foreach($calls as $c) {
            $response = $this->userRequest($response)
                ->get('/api/v1/search' . $c['url']);

            $this->assertStatus($response, 403);
            $response->assertSimilarJson([
                'error' => $c['error']
            ]);
        }
    }
    /**
     *
     *
     * @return void
     */
    public function testExceptions()
    {
        $lang = User::first()->getLanguage();
        ThLanguage::where('short_name', $lang)->delete();
        $calls = [
            ['url' => '/label', 'error' => 'Your language does not exist in ThesauRex'],
            ['url' => '/selection/99', 'error' => 'This concept does not exist'],
        ];

        $response = null;
        foreach($calls as $c) {
            $response = $this->userRequest($response)
                ->get('/api/v1/search' . $c['url']);

            $this->assertStatus($response, 400);
            $response->assertSimilarJson([
                'error' => $c['error']
            ]);
        }
    }
}
