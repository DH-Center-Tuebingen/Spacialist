<?php

namespace Tests\Feature;

use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

use App\User;
use App\Preference;
use App\UserPreference;

class ApiPreferenceTest extends TestCase
{
    // Testing GET requests

    /**
     * @testdox GET    /api/v1/preferences : Get System Preferences
     *
     * @return void
     */
    public function testPreferenceEndpoint()
    {
        $cnt = UserPreference::count();
        $this->assertEquals($cnt, 0);

        $fields = [
            'pref_id' => 1,
            'user_id' => 1,
            'value' => '{"language_key":"de"}'
        ];
        $up = new UserPreference();
        foreach($fields as $k => $v) {
            $up->{$k} = $v;
        }
        $up->save();

        $cnt = UserPreference::count();
        $this->assertEquals($cnt, 1);

        $response = $this->userRequest()
            ->get('/api/v1/preference');

        $response->assertJsonCount(10);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'label',
                'value',
                'created_at',
                'updated_at'
            ]
        ]);

        $response->assertJson(fn(AssertableJson $json) =>
            $json
                ->etc()
                ->has('prefs.gui-language', fn(AssertableJson $guiJson) =>
                $guiJson->where('id', 1)
                    ->where('value', 'en')
                    ->etc()
            )
                ->has('prefs.columns', fn(AssertableJson $colJson) =>
                    $colJson->where('id', 2)
                        ->where('value', [
                            'left' => 2,
                            'center' => 5,
                            'right' => 5,
                        ])
                        ->etc()
                )
                ->has('prefs.show-tooltips', fn(AssertableJson $tooltipJson) =>
                    $tooltipJson->where('id', 3)
                        ->where('value', true)
                        ->etc()
                )
                ->has('prefs.tag-root', fn(AssertableJson $tagJson) =>
                    $tagJson->where('id', 4)
                        ->where('value', 'https://spacialist.escience.uni-tuebingen.de/<user-project>/eigenschaften#20171220100251')
                        ->etc()
                )
                ->has('prefs.link-to-thesaurex', fn(AssertableJson $thJson) =>
                    $thJson->where('id', 5)
                        ->where('value', '')
                        ->etc()
                )
                ->has('prefs.project-name', fn(AssertableJson $projectNameJson) =>
                    $projectNameJson->where('id', 6)
                        ->where('value', 'Spacialist')
                        ->etc()
                )
                ->has('prefs.project-maintainer', fn(AssertableJson $maintainerJson) =>
                    $maintainerJson->where('id', 7)
                        ->where('value', [
                            'name' => '',
                            'email' => '',
                            'public' => false,
                            'description' => '',
                        ])
                        ->etc()
                )
                ->has('plugin.map.prefs.map-projection', fn(AssertableJson $mapJson) =>
                    $mapJson->where('id', 8)
                        ->where('value', [
                            'epsg' => '4326'
                        ])
                        ->etc()
                )
                ->has('prefs.enable-password-reset-link', fn(AssertableJson $pwJson) =>
                    $pwJson->where('id', 9)
                        ->where('value', false)
                        ->etc()
                )
                ->has('prefs.color', fn(AssertableJson $colorJson) =>
                    $colorJson->where('id', 10)
                        ->where('value', '')
                        ->etc()
                )
        );
    }

    /**
     * @testdox PATCH  /api/v1/preference : Change System Preference
     *
     * @return void
     */
    public function testPatchSystemPreferenceEndpoint()
    {
        $data = [
            'changes' => [
                [
                    'user' => false,
                    'value' => 'Updated Project Name',
                    'label' => 'prefs.project-name',
                ],
            ],
        ];

        $response = $this->userRequest()
            ->patch('/api/v1/preference', $data);

        $projectNamePref = Preference::find(6);
        $this->assertEquals('prefs.project-name', $projectNamePref->label);
        $this->assertEquals('{"name": "Updated Project Name"}', $projectNamePref->default_value);
    }

    /**
     * @testdox PATCH  /api/v1/preference : Change User Preference
     *
     * @return void
     */
    public function testPatchUserPreferenceEndpoint()
    {
        $data = [
            'changes' => [
                [
                    'user' => true,
                    'value' => '{"left": 2, "right": 2, "center": 8}',
                    'label' => 'prefs.columns',
                ],
            ],
        ];

        $response = $this->userRequest()
            ->patch('/api/v1/preference', $data);

        $pref = User::with('preferences')->first()->preferences[0];
        $this->assertEquals(2, $pref->pref_id);
        $this->assertEquals(1, $pref->user_id);
        $this->assertEquals('"{\\"left\\": 2, \\"right\\": 2, \\"center\\": 8}"', $pref->value);
    }

    // Testing exceptions and permissions

    /**
     * @testdox Test Permissions
     *
     * @return void
     */
    public function testPermissions()
    {
        User::first()->roles()->detach();

        $calls = [
            ['url' => '', 'error' => 'You do not have the permission to edit system preferences', 'verb' => 'patch'],
        ];

        $response = null;
        foreach($calls as $c) {
            $response = $this->userRequest($response)
                ->json($c['verb'], '/api/v1/preference' . $c['url'], [
                    'changes' => [
                        ['label' => 'prefs.columns']
                    ],
                ]);

            $response->assertStatus(403);
            $response->assertSimilarJson([
                'error' => $c['error']
            ]);
        }
    }
    /**
     * @testdox Test Exceptions
     *
     * @return void
     */
    public function testExceptions()
    {
        $calls = [
            ['url' => '', 'error' => 'This preference does not exist', 'verb' => 'patch'],
        ];

        $response = null;
        foreach($calls as $c) {
            $response = $this->userRequest($response)
                ->json($c['verb'], '/api/v1/preference' . $c['url'], [
                    'changes' => [
                        ['label' => 'prefs.columnsWrongName']
                    ]
                ]);

            $this->assertStatus($response, 400);
            $response->assertSimilarJson([
                'error' => $c['error']
            ]);
        }
    }
}
