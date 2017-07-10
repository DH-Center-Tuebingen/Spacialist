<?php

class TestCase extends Laravel\Lumen\Testing\TestCase
{
    protected $faker;
    protected $user;

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    public function setUp() {
        parent::setUp();

        $this->faker = Faker\Factory::create();

        $testEmail = 'test1234@user.com';

        // Create new User with Admin Role
        $this->user = App\User::where('email', $testEmail)->first();
        if($this->user === null) {
            $this->user = factory(App\User::class)->make([
                'name' => 'Test User',
                'email' => $testEmail
            ]);
            $this->user->save();
            $this->user->attachRole(App\Role::where('name', 'admin')->first());
        }
    }
}
