<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Literature;
use App\Helpers;

class AddBibtexKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add citation key as nullable, because we have to update existing entries
        Schema::table('literature', function (Blueprint $table) {
            $table->text('citekey')->nullable();
        });
        // compute citation key
        $entries = Literature::all();
        foreach($entries as $entry) {
            $ckey = Helpers::computeCitationKey($entry->toArray());
            $entry->citekey = $ckey;
            $entry->save();
        }
        // set citation keys unique and not nullable
        Schema::table('literature', function (Blueprint $table) {
            $table->text('citekey')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('literature', function (Blueprint $table) {
            $table->dropColumn('citekey');
        });
    }
}
