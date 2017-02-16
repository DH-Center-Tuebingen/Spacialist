<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call('LanguageTableSeeder');
        $this->call('RandomSeeder');
        $user = factory(App\User::class, 'guest')->create();
        $user->attachRole(App\Role::where('name', 'guest')->first());
    }

}
