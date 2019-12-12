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
        $preference = new Preference();
        $preference->label = 'prefs.enable-password-reset-link';
        $preference->default_value = json_encode([
            'use' => false
        ]);
        $preference->allow_override = false;
        $preference->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Preference::where('label', 'prefs.enable-password-reset-link')
            ->delete();
    }
}
