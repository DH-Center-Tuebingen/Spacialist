<?php

use App\Preference;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    const migrationNames = [
        '2019_03_14_140815_fix_geodata_foreign_key',
    ];
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        activity()->disableLogging();

        DB::table('migrations')
            ->whereIn('migration', self::migrationNames)
            ->delete();
        
        Preference::where('label', 'prefs.load-extensions')->delete();

        activity()->enableLogging();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
