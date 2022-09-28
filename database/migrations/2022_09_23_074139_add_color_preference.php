<?php

use App\Preference;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add Color Preference
        $label = 'prefs.color';
        $value = json_encode(['color_key' => '']);
        $override = false;

        $preference = new Preference();
        $preference->label = $label;
        $preference->default_value = $value;
        $preference->allow_override = $override;
        $preference->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove Color Preference
        Preference::where('label', 'prefs.color')->delete();
    }
};
