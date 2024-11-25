<?php

namespace Tests;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;
use Database\Seeders\TestSeeder;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

abstract class TestCase extends BaseTestCase {
    use CreatesApplication;
    use WithFaker;
    use DatabaseTransactions;
    use ArraySubsetAsserts;

    /**
    * Indicates whether the default seeder should run before each test.
    *
    * @var bool
    */
    protected $seed = true;

    /**
     * Specify the seeder that should be run.
     */
    protected $seeder = TestSeeder::class;

    protected $connectionsToTransact = [
        'testing'
    ];

    public $user = null;
    public $token = null;

    protected function setUp(): void {
        parent::setUp();
        $this->user = null;
        $this->token = null;
        $this->getUserToken();
    }

    protected function refreshToken($response) {
        $this->token = substr($response->headers->get('authorization'), 7);
    }

    protected function getStreamedContent($response) {
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();
        return $content;
    }

    public function getUserToken() {
        if(!isset($this->user)) {
            $this->user = User::find(1);
        }
        $this->token = JWTAuth::fromUser($this->user);
    }

    public function getUnauthUser() {
        if(!isset($this->user)) {
            $this->user = User::find(1);
        }
        return $this->user;
    }

    public function userRequest($response = null) {
        if(isset($response)) {
            $this->refreshToken($response);
        }

        return $this->withHeaders([
            'Authorization' => "Bearer $this->token",
            'Accept' => 'application/json' // When not setting this, Laravels validation will return a 302 on failure!
        ]);
    }
}