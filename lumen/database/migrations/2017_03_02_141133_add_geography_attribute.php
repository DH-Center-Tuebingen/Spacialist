<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Phaza\LaravelPostgis\Geometries;

class AddGeographyAttribute extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // enable the postgis extension
        Schema::getConnection()->statement('CREATE EXTENSION IF NOT EXISTS postgis');

        Schema::table('attribute_values', function (Blueprint $table) {
            $table->geography('geography_val')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attribute_values', function (Blueprint $table) {
            $table->dropColumn('geography_val');
        });
    }
}
