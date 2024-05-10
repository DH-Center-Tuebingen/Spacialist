<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\Models\Activity;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /*
        The following activites were added by the system and thus are not part of the user's log

        description	subject_id	subject_type
        "created"	1	"App\Role"
        "created"	2	"App\Role"
        "created"	1	"App\User"
        "created"	1	"App\ThLanguage"
        "created"	2	"App\ThLanguage"
        "created"	1	"App\AvailableLayer"
        "created"	2	"App\AvailableLayer"
        */
        activity()->disableLogging();

        Activity::where('description', 'created')
            ->whereIn('subject_id', [1, 2])
            ->whereIn('subject_type', ['App\Role', 'App\ThLanguage', 'App\AvailableLayer'])
            ->delete();

        Activity::where('description', 'created')
            ->where('subject_id', 1)
            ->where('subject_type', 'App\User')
            ->delete();

        activity()->enableLogging();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
