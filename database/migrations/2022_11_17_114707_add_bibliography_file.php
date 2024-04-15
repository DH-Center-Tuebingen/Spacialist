<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

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
            $table->text('file')->nullable();
            $table->text('doi')->nullable();
            $table->text('email')->nullable();
            $table->text('subtype')->nullable();
            $table->text('url')->nullable();
        });

        Storage::makeDirectory('bibliography');

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
            $table->dropColumn('file');
            $table->dropColumn('doi');
            $table->dropColumn('email');
            $table->dropColumn('subtype');
            $table->dropColumn('url');
        });

        Storage::deleteDirectory('bibliography');

        activity()->enableLogging();
    }
};
