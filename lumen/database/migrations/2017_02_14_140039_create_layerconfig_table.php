<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLayerconfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('available_layers', function (Blueprint $table) {
            $table->increments('id');
            $table->text('name');
            $table->text('url');
            $table->text('type');
            $table->text('subdomains')->nullable();
            $table->text('attribution')->nullable();
            $table->float('opacity')->nullable()->default(1);
            $table->text('layers')->nullable();
            $table->text('styles')->nullable();
            $table->text('format')->nullable();
            $table->text('version')->nullable();
            $table->boolean('visible')->nullable()->default(true);
            $table->boolean('is_overlay')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('available_layers');
    }
}
