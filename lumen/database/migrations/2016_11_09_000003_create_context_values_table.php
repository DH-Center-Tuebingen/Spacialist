<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContextValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('context_values', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('find_id')->unsigned();
            $table->integer('attribute_id')->unsigned();
            $table->string('str_val', 256);
            $table->integer('int_val');
            $table->double('dbl_val');
            $table->timestampTz('dt_val');
            $table->integer('find_val')->unsigned();
            $table->text('th_val');
            $table->timestamps();

            $table->foreign('find_id')->references('id')->on('finds')->onDelete('cascade');
            $table->foreign('attribute_id')->references('id')->on('attributes')->onDelete('cascade');
            $table->foreign('find_val')->references('id')->on('finds')->onDelete('cascade');
            $table->foreign('th_val')->references('concept_url')->on('th_concept');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('context_values');
    }
}
