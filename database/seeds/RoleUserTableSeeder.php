<?php

use Illuminate\Database\Seeder;

class RoleUserTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('role_user')->insert(array (
            0 =>
            array (
                'user_id' => 1,
                'role_id' => 1,
            ),
        ));
    }
}
