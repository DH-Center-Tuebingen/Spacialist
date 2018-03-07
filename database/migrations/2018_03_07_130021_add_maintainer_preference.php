<?php

use App\Preference;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMaintainerPreference extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $label = 'prefs.project-maintainer';
        $value = json_encode([
            'name' => '',
            'email' => '',
            'description' => '',
            'public' => false
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
            $preference = Preference::where('label', 'prefs.project-maintainer')->firstOrFail();
            $preference->delete();
        } catch(ModelNotFoundException $e) {
        }
    }
}
