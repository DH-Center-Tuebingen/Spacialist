<?php

use Illuminate\Database\Seeder;

class LanguageTableSeeder extends Seeder
{
    public function run() {
        $de = new App\ThLanguage;
        $de->lasteditor     = 'postgres';
        $de->display_name   = 'Deutsch';
        $de->short_name     = 'de';
        $de->save();
        $en = new App\ThLanguage;
        $en->lasteditor    = 'postgres';
        $en->display_name  = 'English';
        $en->short_name    = 'en';
        $en->save();
        $es = new App\ThLanguage;
        $es->lasteditor    = 'postgres';
        $es->display_name  = 'Español';
        $es->short_name    = 'es';
        $es->save();
        $fr = new App\ThLanguage;
        $fr->lasteditor    = 'postgres';
        $fr->display_name  = 'Français';
        $fr->short_name    = 'fr';
        $fr->save();
        $it = new App\ThLanguage;
        $it->lasteditor    = 'postgres';
        $it->display_name  = 'Italiano';
        $it->short_name    = 'it';
        $it->save();
    }
}
