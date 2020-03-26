<?php

use App\Preference;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EnablePasswordResetLinkPreference extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        activity()->disableLogging();

        $preference = new Preference();
        $preference->label = 'prefs.enable-password-reset-link';
        $preference->default_value = json_encode([
            'use' => false
        ]);
        $preference->allow_override = false;
        $preference->save();

        activity()->enableLogging();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        activity()->disableLogging();

        Preference::where('label', 'prefs.enable-password-reset-link')
            ->delete();

        activity()->enableLogging();
    }
}
