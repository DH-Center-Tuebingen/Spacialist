<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Mockery as m;

class UserControllerTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGetLoggedInUser()
    {
        $this->withoutMiddleware();
        $user = factory(App\User::class)->create();
        $req = 'user/get/roles/'.$user['id'];
        \Log::info(print_r($user, true));
        \Log::info($req);
        $response = $this->get($req);
        $this->assertResponseOk();
    }
}
