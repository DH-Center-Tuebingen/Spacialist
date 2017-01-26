<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Phaza\LaravelPostgis\Geometries;
use Phaza\LaravelPostgis\Geometries\Point;
use Phaza\LaravelPostgis\Geometries\Geography;


class CreateGeodata extends Migration
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

        // create new table
        Schema::create('geodata', function (Blueprint $table){
            $table->increments('id');
            $table->geography('geom');
            $table->timestamps();
        });

        // create foreign key column in contexts table
        Schema::table('contexts', function (Blueprint $table) {
            $table->integer('geodata_id')->unsigned()->nullable();
            $table->foreign('geodata_id')->references('id')->on('geodata');
        });

        // Get records from old columns
        $contexts = DB::table('contexts')->get();

        // transfer existing geodata to the new table
        foreach($contexts as $context)
        {
            if(isset($context->lat, $context->lng)){
                $entry = new App\Geodata();
                $entry->geom = new Point($context->lat, $context->lng);
                $entry->save();

                DB::table('contexts')
                    ->where('id', $context->id)
                    ->update(['geodata_id' => $entry->id]);
            }
        }

        // Delete old columns
        Schema::table('contexts', function($table)
        {
            $table->dropColumn('lat');
            $table->dropColumn('lng');
        });
        }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // create lat and lon columns
        Schema::table('contexts', function (Blueprint $table) {
            $table->double('lat')->nullable();
            $table->double('lng')->nullable();
        });

        $geodata = App\Geodata::all();

        foreach($geodata as $entry)
        {
            if($entry->geom instanceof Point){
                $lat = $entry->geom->getLat();
                $lng = $entry->geom->getLng();
                DB::table('contexts')
                    ->where('geodata_id', $entry->id)
                    ->update([  'lat' => $lat,
                                'lng' => $lng]);
            }
        }

        // drop the foreign key column
        Schema::table('contexts', function($table)
        {
            $table->dropColumn('geodata_id');
        });

        // drop the geodata table
        Schema::dropIfExists('geodata');

        // drop the extension
        Schema::getConnection()->statement('DROP EXTENSION IF EXISTS postgis');
    }
}
