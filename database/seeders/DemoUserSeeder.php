<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoUserSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'id' => 2,
                'name' => 'John Doe',
                'nickname' => 'johndoe',
                'email' => 'john.doe@example.com',
                'metadata' => NULL,
                'password' => bcrypt('password'),
                'remember_token' => NULL,
                'created_at' => '2018-12-31 12:00:00',
                'updated_at' => '2018-12-31 12:00:00',
                'deleted_at' => NULL,
            ],
            [
                'id' => 3,
                'name' => 'Gary Guest',
                'nickname' => 'garyguest',
                'email' => 'gary.guest@mail.com',
                'metadata' => NULL,
                'password' => bcrypt('password_gary'),
                'remember_token' => NULL,
                'created_at' => '2019-01-06 12:00:00',
                'updated_at' => '2019-01-06 12:00:00',
                'deleted_at' => NULL,
            ]
        ]);
    }
}
