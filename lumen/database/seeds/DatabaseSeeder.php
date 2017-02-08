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
        // $this->call('LanguageTableSeeder');
        $this->call('TestSeeder');

        // $this->call('ContextTableSeeder');
        // // $this->call('ThesaurusSeeder');
        // //
        App\User::select()->delete();
        $user = factory(App\User::class, 'test')->create();
        $user->attachRole(App\Role::where('name', 'employee')->first());
    }

}
