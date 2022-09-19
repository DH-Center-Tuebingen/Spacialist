<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeLayerUrlNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        activity()->disableLogging();

        Schema::table('available_layers', function (Blueprint $table) {
            $table->text('url')->nullable()->change();
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

        Schema::table('available_layers', function (Blueprint $table) {
            $table->text('url')->nullable(false)->change();
        });

        activity()->enableLogging();
    }
}
