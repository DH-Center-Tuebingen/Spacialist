<?php

use App\Preference;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        activity()->disableLogging();

        Schema::table('preferences', function (Blueprint $table) {
            $table->dropColumn('allow_override');
        });

        Preference::where('label', 'prefs.map-projection')
            ->update(['label' => 'plugin.map.prefs.map-projection']);

        activity()->enableLogging();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        activity()->disableLogging();

        Schema::table('preferences', function (Blueprint $table) {
            $table->boolean('allow_override')->nullable()->default(false);
        });

        activity()->enableLogging();
    }
};
