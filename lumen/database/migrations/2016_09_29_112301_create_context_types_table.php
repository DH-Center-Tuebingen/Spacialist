<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContextTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('context_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('thesaurus_id', 256);
            $table->integer('type')->unsigned();
            $table->timestamps();

            $table->foreign('thesaurus_id')->references('concept_url')->on('th_concept');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('context_types');
    }
}
