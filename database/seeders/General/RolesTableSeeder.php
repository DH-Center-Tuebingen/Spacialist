<?php

namespace Database\Seeders\General;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert(array (
            0 =>
            array (
                'id' => 1,
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Project Administrator',
                'guard_name' => 'web',
                'created_at' => '2017-12-20 09:47:35',
                'updated_at' => '2017-12-20 09:47:35',
            ),
            1 =>
            array (
                'id' => 2,
                'name' => 'guest',
                'display_name' => 'Guest',
                'description' => 'Guest User',
                'guard_name' => 'web',
                'created_at' => '2017-12-20 09:47:35',
                'updated_at' => '2017-12-20 09:47:35',
            ),
        ));
    }
}
