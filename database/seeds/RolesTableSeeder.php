<?php

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('roles')->insert(array (
            0 =>
            array (
                'id' => 1,
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Project Administrator',
                'created_at' => '2017-12-20 09:47:35',
                'updated_at' => '2017-12-20 09:47:35',
            ),
            1 =>
            array (
                'id' => 2,
                'name' => 'guest',
                'display_name' => 'Guest',
                'description' => 'Guest User',
                'created_at' => '2017-12-20 09:47:35',
                'updated_at' => '2017-12-20 09:47:35',
            ),
        ));
    }
}
