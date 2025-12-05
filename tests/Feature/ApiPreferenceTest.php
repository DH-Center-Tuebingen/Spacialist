<?php

use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\TestDox;

use App\User;
use App\Preference;
use App\UserPreference;


test('preference endpoint', function () {
    $cnt = UserPreference::count();
    expect(0)->toEqual($cnt);

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
    expect(1)->toEqual($cnt);

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
});

test('patch system preference endpoint', function () {
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
    expect($projectNamePref->label)->toEqual('prefs.project-name');
    expect($projectNamePref->default_value)->toEqual('{"name": "Updated Project Name"}');
});

test('patch user preference endpoint', function () {
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
    expect($pref->pref_id)->toEqual(2);
    expect($pref->user_id)->toEqual(1);
    expect($pref->value)->toEqual('"{\\"left\\": 2, \\"right\\": 2, \\"center\\": 8}"');
});

test('permissions', function () {
    User::first()->roles()->detach();

    $calls = [
        ['url' => '', 'error' => 'You do not have the permission to edit system preferences', 'verb' => 'patch'],
    ];

    $response = null;
    foreach($calls as $c) {
        $response = $this->userRequest()
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
});

test('exceptions', function () {
    $calls = [
        ['url' => '', 'error' => 'This preference does not exist', 'verb' => 'patch'],
    ];

    $response = null;
    foreach($calls as $c) {
        $response = $this->userRequest()
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
});
