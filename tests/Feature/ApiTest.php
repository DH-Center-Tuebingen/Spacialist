<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Support\Str;

use App\VersionInfo;

class ApiTest extends TestCase
{
    /**
     * @testdox GET    / : Get Base App Endpoint
     *
     * @return void
     */
    public function testApiRoot()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * @testdox GET    /welcome : Get Welcome Page Endpoint
     *
     * @return void
     */
    public function testWelcomePage()
    {
        $response = $this->get('/welcome');

        $response->assertStatus(200);
    }

    /**
     * @testdox GET    /api/v1/pre : Get Pre Endpoint Failed Unauth
     *
     * @return void
     */
    public function testUnauthPreRequest()
    {
        $this->unsetTestUser();
        // Unauthenticated request redirects to /login
        $response = $this->get('/api/v1/pre');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * @testdox GET    /api/v1/pre : Get Pre Endpoint
     *
     * @return void
     */
    public function testAuthPreRequest()
    {
        $response = $this->userRequest()
            ->get('/api/v1/pre');

        $response->assertStatus(200);
        $response->assertJsonCount(7);
        $response->assertJsonStructure([
            'system_preferences',
            'preferences',
            'concepts',
            'entityTypes',
            'datatype_data',
            'colorsets',
            'analysis',
        ]);
    }

    /**
     * @testdox GET    /api/v1/version : Get Version Endpoint
     *
     * @return void
     */
    public function testVersionRequest()
    {
        $vi = new VersionInfo();
        $response = $this->userRequest()
            ->get('/api/v1/version');

        $response->assertStatus(200);
        $content = $response->decodeResponseJson();
        $this->assertMatchesRegularExpression('/^\d+$/', $content['time']);
        $this->assertMatchesRegularExpression('/^[A-ZÄÖÜ][a-zäöüß]+$/', $content['name']);
        $this->assertMatchesRegularExpression('/^v\d+\.\d+\.\d+$/', $content['release']);
        $this->assertMatchesRegularExpression('/^v\d+\.\d+\.\d+ \([A-ZÄÖÜ][a-zäöüß]+\)$/', $content['readable']);
        $this->assertMatchesRegularExpression('/^v\d+\.\d+\.\d+-[a-zäöüß]+(-g[a-f0-9]{8})?$/', $content['full']);
        $this->assertEquals($content['release'], 'v' . $vi->getMajor() . "." . $vi->getMinor() . "." . $vi->getPatch());

        $hash = $vi->getReleaseHash();
        if(isset($hash)) {
            $this->assertTrue(Str::endsWith($content['full'], $hash));
        }
    }
}
