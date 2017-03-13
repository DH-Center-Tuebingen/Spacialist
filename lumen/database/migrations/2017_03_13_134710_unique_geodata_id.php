<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UniqueGeodataId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contexts', function (Blueprint $table) {
            $table->integer('geodata_id')->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contexts', function (Blueprint $table) {
            $table->dropUnique('contexts_geodata_id_unique');
        });
    }
}
