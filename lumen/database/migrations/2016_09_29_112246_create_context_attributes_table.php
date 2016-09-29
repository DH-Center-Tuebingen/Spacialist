<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContextAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('context_attributes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('context_id')->unsigned();
            $table->integer('attribute_id')->unsigned();
            $table->timestamps();

            $table->foreign('context_id')->references('id')->on('context_types');
            $table->foreign('attribute_id')->references('id')->on('attributes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('context_attributes');
    }
}
