<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sources', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('find_id')->unsigned();
            $table->integer('attribute_id')->unsigned();
            $table->integer('literature_id')->unsigned();
            $table->string('description', 1024);
            $table->timestamps();

            $table->foreign('find_id')->references('id')->on('finds');
            $table->foreign('attribute_id')->references('id')->on('attributes');
            $table->foreign('literature_id')->references('id')->on('bib_tex');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sources');
    }
}
