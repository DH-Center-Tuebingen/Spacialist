<?php

use App\Preference;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProjectionPref extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $label = 'prefs.map-projection';
        $value = json_encode([
            'epsg' => '4326'
        ]);
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
        try {
            $preference = Preference::where('label', 'prefs.map-projection')->firstOrFail();
            $preference->delete();
        } catch(ModelNotFoundException $e) {
        }
    }
}
