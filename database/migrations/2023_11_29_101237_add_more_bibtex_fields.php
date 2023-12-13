<?php

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
        activity()->disableLogging();

        Schema::table('bibliography', function (Blueprint $table) {
            $table->text('issn')->nullable();
            $table->text('isbn')->nullable();
            $table->text('language')->nullable();
            $table->text('abstract')->nullable();
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

        Schema::table('bibliography', function (Blueprint $table) {
            $table->dropColumn('issn');
            $table->dropColumn('isbn');
            $table->dropColumn('language');
            $table->dropColumn('abstract');
        });

        activity()->enableLogging();
    }
};
