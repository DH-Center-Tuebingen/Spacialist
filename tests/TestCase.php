<?php

namespace Tests;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

use App\User;
use Database\Seeders\TestingSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase {
    use CreatesApplication;
    use WithFaker;
    use RefreshDatabase;

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
        } catch(\Exception $e) {
            // No error message found in response
        }

        $this->assertSame($status, $response->getStatusCode(), $message == "" ? $no_errors : $message);
    }

    public function setTestUser($id = 1) {
        if(!isset($this->user)) {
            $this->user = User::find($id);
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
    
    public function useGuest(){
        $this->unsetTestUser();
        $this->setTestUser(2);
    }

    public function useUserWithPermissions(string | array $permissions) {
        $this->unsetTestUser();
        $user = User::factory()->create();
        $user->givePermissionTo($permissions);
        $this->setTestUser($user->id);
    }
    
    /**
     * Use a user that has all group permissions except the ones specified in the argument using a shorthand string (e.g. "rwds").
     * 
     * @param $group string The permission group to use, e.g. "plugin"
     * @param $without stringThe permissions to revoke RWDS (Read, Write, Delete, Share) 
     */
    public function useUserWithoutPermission(string $group, string $without) {
        $without = strtolower($without);    
        $permissions = [
            'r' => $group . '_read',
            'c' => $group . '_create',
            'w' => $group . '_write',
            'd' => $group . '_delete',
            's' => $group . '_share',
        ];   
        
        while(strlen($without) > 0) {
            $char = substr($without, 0, 1);
            if(isset($permissions[$char])) {
                unset($permissions[$char]);
            }
            $without = substr($without, 1);
        }
    
        $this->unsetTestUser();
        $user = User::factory()->create();
        $user->givePermissionTo(array_values($permissions));
        $this->setTestUser($user->id);
    }
    
    /**
     * The booted method of laravel models is called before the testCase is run.
     * Therefore when you need to modify any model and have those changes being
     * available in the booted function of that model. You need to "reboot" the model.
     */
    public static function rebootModel($modelClass) {
        // Clear global scopes before rebooting
        $reflection = new \ReflectionClass($modelClass);
        
        // Clear global scopes
        $scopesProperty = $reflection->getProperty('globalScopes');
        $scopesProperty->setAccessible(true);
        $scopesProperty->setValue(null, []);
        
        // Flush event listeners and reboot
        $modelClass::flushEventListeners();
        $modelClass::boot();
    }
}