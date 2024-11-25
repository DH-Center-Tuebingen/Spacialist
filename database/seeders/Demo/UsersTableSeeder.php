<?php

namespace Database\Seeders\Demo;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert(array (
            0 =>
            array (
                'id' => 1,
                'name' => 'Admin',
                'nickname' => 'admin',
                'email' => 'admin@localhost',
                'metadata' => NULL,
                'password' => '$2y$10$GTwOUwcsqgqL/J/0dxxkIenBLR3Pna9ZfIa7sVyPkJgQDwqLU0wqa',
                'remember_token' => NULL,
                'created_at' => '2017-12-20 09:47:36',
                'updated_at' => '2017-12-20 09:47:36',
                'deleted_at' => NULL,
            ),
            array (
                'id' => 2,
                'name' => 'First User',
                'nickname' => 'first_user',
                'email' => 'first@localhost',
                'metadata' => NULL,
                'password' => '$2y$10$GTwOUwcsqgqL/J/0dxxkIenBLR3Pna9ZfIa7sVyPkJgQDwqLU0wqa',
                'remember_token' => NULL,
                'created_at' => '2017-12-31 09:47:36',
                'updated_at' => '2017-12-31 09:47:36',
                'deleted_at' => NULL,
            ),
            array (
                'id' => 3,
                'name' => 'Deleted User',
                'nickname' => 'deleted_user',
                'email' => 'deleted@localhost',
                'metadata' => NULL,
                'password' => '$2y$10$GTwOUwcsqgqL/J/0dxxkIenBLR3Pna9ZfIa7sVyPkJgQDwqLU0wqa',
                'remember_token' => NULL,
                'created_at' => '2017-12-31 09:47:36',
                'updated_at' => '2017-12-31 09:47:36',
                'deleted_at' => '2018-01-10 09:47:36',
            ),
        ));
    }
}
