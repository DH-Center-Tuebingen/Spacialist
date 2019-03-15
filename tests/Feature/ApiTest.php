<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Preference;

class ApiTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testApiRoot()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * Test welcome page.
     *
     * @return void
     */
    public function testWelcomePage()
    {
        $response = $this->get('/welcome');

        $response->assertStatus(200);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testUnauthPreRequest()
    {
        // Unauthenticated request redirects to /login
        $response = $this->get('/api/v1/pre');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testAuthPreRequestWithoutToken()
    {
        $response = $this->actingAs($this->user)
            ->get('/api/v1/pre');

        $response->assertStatus(401);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testAuthPreRequest()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/pre');

        $response->assertStatus(200);
        // returns array with 'preferences', 'concepts' and 'entityTypes'
        $response->assertJsonCount(3);
        $response->assertJsonStructure([
            'preferences',
            'concepts',
            'entityTypes',
        ]);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testVersionRequest()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/version');

        $response->assertStatus(200);
        $content = $response->decodeResponseJson();
        $this->assertRegExp('/^\d+$/', $content['time']);
        $this->assertRegExp('/^[A-Z][a-z]+$/', $content['name']);
        $this->assertRegExp('/^v\d\.\d\.\d$/', $content['release']);
        $this->assertRegExp('/^v\d\.\d\.\d \(\w+\)$/', $content['readable']);
        $this->assertRegExp('/^v\d\.\d\.\d-\w+-g[a-f0-9]{7}$/', $content['full']);
    }
}
