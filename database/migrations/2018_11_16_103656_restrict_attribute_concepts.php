<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RestrictAttributeConcepts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        activity()->disableLogging();

        Schema::table('attributes', function (Blueprint $table) {
            $table->boolean('recursive')->nullable()->default(true);
        });

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

        Schema::table('attributes', function (Blueprint $table) {
            $table->dropColumn('recursive');
        });

        activity()->enableLogging();
    }
}
