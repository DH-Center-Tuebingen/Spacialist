<?php

use Illuminate\Database\Seeder;

class ThLanguageTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('th_language')->insert(array (
            0 =>
            array (
                'id' => 1,
                'lasteditor' => 'postgres',
                'display_name' => 'Deutsch',
                'short_name' => 'de',
                'created_at' => '2017-12-20 09:47:35',
                'updated_at' => '2017-12-20 09:47:35',
            ),
            1 =>
            array (
                'id' => 2,
                'lasteditor' => 'postgres',
                'display_name' => 'English',
                'short_name' => 'en',
                'created_at' => '2017-12-20 09:47:35',
                'updated_at' => '2017-12-20 09:47:35',
            ),
            2 =>
            array (
                'id' => 3,
                'lasteditor' => 'postgres',
                'display_name' => 'Español',
                'short_name' => 'es',
                'created_at' => '2017-12-20 09:47:35',
                'updated_at' => '2017-12-20 09:47:35',
            ),
            3 =>
            array (
                'id' => 4,
                'lasteditor' => 'postgres',
                'display_name' => 'Français',
                'short_name' => 'fr',
                'created_at' => '2017-12-20 09:47:35',
                'updated_at' => '2017-12-20 09:47:35',
            ),
            4 =>
            array (
                'id' => 5,
                'lasteditor' => 'postgres',
                'display_name' => 'Italiano',
                'short_name' => 'it',
                'created_at' => '2017-12-20 09:47:35',
                'updated_at' => '2017-12-20 09:47:35',
            ),
        ));
    }
}
