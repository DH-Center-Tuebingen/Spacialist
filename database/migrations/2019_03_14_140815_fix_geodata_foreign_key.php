<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixGeodataForeignKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        activity()->disableLogging();

        Schema::table('entities', function (Blueprint $table) {
            $table->dropForeign('entities_geodata_id_foreign');

            $table->foreign('geodata_id')->references('id')->on('geodata')->onDelete('set null');
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

        Schema::table('entities', function (Blueprint $table) {
            $table->dropForeign('entities_geodata_id_foreign');

            $table->foreign('geodata_id')->references('id')->on('geodata');
        });

        activity()->enableLogging();
    }
}
