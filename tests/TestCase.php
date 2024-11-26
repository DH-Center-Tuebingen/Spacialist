<?php

namespace Tests;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

use App\User;
use Database\Seeders\TestingSeeder;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Illuminate\Support\Facades\Auth;

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
        $this->setTestUser();
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

    public function setTestUser() {
        if(!isset($this->user)) {
            $this->user = User::find(1);
        }
        Sanctum::actingAs($this->user, [], 'web');
    }

    public function unsetTestUser() {
        if(isset($this->user)) {
            $this->user = null;
        }

        Auth::guard('web')->logout(true);
        }

    public function userRequest() {
        return $this->withHeaders([
            'Accept' => 'application/json' // When not setting this, Laravels validation will return a 302 on failure!
        ]);
    }
}