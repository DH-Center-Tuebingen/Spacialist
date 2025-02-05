<?php

namespace Tests;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\User;
use Database\Seeders\TestingSeeder;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

abstract class TestCase extends BaseTestCase {
    use CreatesApplication;
    use WithFaker;
    use RefreshDatabase;
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
    protected $seeder = TestingSeeder::class;

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

    public function assertStatus($response, $status) {
        $no_errors = "No error message found in response.";
        $message= "";
        try{
            $json = $response->json();

            if(isset($json['message'])) {
                $message .= "Message :: " . $json['message'] . "\n  ";
            }

            if(isset($json['error'])) {
                $message .= "Error :: " . $json['error'] . "\n  ";
            }

            if(isset($json['errors'])) {
                $message .= "Errors \n  ";
                $message .= "============\n  ";
                foreach($json['errors'] as $key => $value) {
                    $message .= "==> ". $key . ":: " . json_encode($value) . "\n  ";
                }

            }
        }catch(\Exception $e) {
            // No error message found in response
        }

        $this->assertSame($status, $response->getStatusCode(), $message == "" ? $no_errors : $message);
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