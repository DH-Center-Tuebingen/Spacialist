<?php

use App\Preference;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        activity()->disableLogging();

        // Add Color Preference
        $label = 'prefs.enable-open-access';
        $value = ['enable' => false];

        $preference = new Preference();
        $preference->label = $label;

        $projectMaintainer = Preference::where('label', 'prefs.project-maintainer')->first();
        $maintainerValue = json_decode($projectMaintainer->default_value, true);

        $value['enable'] = $maintainerValue['public'];
        $preference->default_value = json_encode($value);
        $preference->save();

        unset($maintainerValue['public']);
        $projectMaintainer->default_value = json_encode($maintainerValue);
        $projectMaintainer->saveQuietly();

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

        $preference = Preference::where('label', 'prefs.enable-open-access')->first();
        $projectMaintainer = Preference::where('label', 'prefs.project-maintainer')->first();
        $maintainerValue = json_decode($projectMaintainer->default_value, true);
        $maintainerValue['public'] = json_decode($preference->default_value)->enable;
        $projectMaintainer->default_value = json_encode($maintainerValue);
        $projectMaintainer->saveQuietly();
        $preference->delete();

        activity()->enableLogging();
    }
};
