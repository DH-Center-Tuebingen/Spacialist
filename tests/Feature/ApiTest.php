<?php

use Illuminate\Support\Str;

use App\VersionInfo;


test('api root', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('welcome page', function () {
    $response = $this->get('/welcome');

    $response->assertStatus(200);
});

test('unauth pre request', function () {
    $this->unsetTestUser();

    // Unauthenticated request redirects to /login
    $response = $this->get('/api/v1/pre');

    $response->assertStatus(302);
    $response->assertRedirect('/login');
});

test('auth pre request', function () {
    $response = $this->userRequest()
        ->get('/api/v1/pre');

    $response->assertStatus(200);
    $response->assertJsonCount(21);
    $response->assertJsonStructure([
        'system_preferences',
        'preferences',
        'concepts',
        'entityTypes',
        'datatype_data',
        'colorsets',
        'analysis',
        'attributes',
        'attributeSelections',
        'users',
        'deleted_users',
        'roles',
        'permissions',
        'presets',
        'topEntities',
        'bibliography',
        'tags',
        'version',
        'plugins',
        'geometryTypes',
        'attributeTypes',
    ]);
});

test('version request', function () {
    $vi = new VersionInfo();
    $response = $this->userRequest()
        ->get('/api/v1/version');

    $response->assertStatus(200);
    $content = $response->decodeResponseJson();
    expect((string)$content['time'])->toMatch('/^\d+$/');
    expect($content['name'])->toMatch('/^[A-ZÄÖÜ][a-zäöüß]+$/');
    expect($content['release'])->toMatch('/^v\d+\.\d+\.\d+$/');
    expect($content['readable'])->toMatch('/^v\d+\.\d+\.\d+ \([A-ZÄÖÜ][a-zäöüß]+\)$/');
    expect($content['full'])->toMatch('/^v\d+\.\d+\.\d+-[a-zäöüß]+(-g[a-f0-9]{8})?$/');
    expect('v' . $vi->getMajor() . "." . $vi->getMinor() . "." . $vi->getPatch())->toEqual($content['release']);

    $hash = $vi->getReleaseHash();
    if(isset($hash)) {
        expect(Str::endsWith($content['full'], $hash))->toBeTrue();
    }
});
