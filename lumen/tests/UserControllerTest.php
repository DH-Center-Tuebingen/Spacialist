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
        $user = factory(App\User::class)->create();

        $response = $this->actingAs($user)
                ->call('POST', '/user/get');

        $this->assertEquals(200, $response->status());
    }
}
