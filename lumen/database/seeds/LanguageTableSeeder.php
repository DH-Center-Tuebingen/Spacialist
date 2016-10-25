<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LanguageTableSeeder extends Seeder
{
    public function run() {
        DB::table('th_language')->delete();
        DB::table('th_language')->create(array(
            'lasteditor'    => 'postgres',
            'display_name'  => 'Deutsch',
            'short_name'    => 'de'
        ));
        DB::table('th_language')->create(array(
            'lasteditor'    => 'postgres',
            'display_name'  => 'English',
            'short_name'    => 'en'
        ));
        DB::table('th_language')->create(array(
           'lasteditor'    => 'postgres',
           'display_name'  => 'Español',
           'short_name'    => 'es'
        ));
        DB::table('th_language')->create(array(
            'lasteditor'    => 'postgres',
            'display_name'  => 'Français',
            'short_name'    => 'fr'
        ));
        DB::table('th_language')->create(array(
            'lasteditor'    => 'postgres',
            'display_name'  => 'Italiano',
            'short_name'    => 'it'
        ));
    }
}
