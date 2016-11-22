<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->increments('id');
            $table->text('thesaurus_id');
            $table->string('datatype', 128);
            $table->text('thesaurus_root_id')->nullable()->comment('only for string-sc and string-mc');
            $table->timestamps();

            $table->foreign('thesaurus_id')->references('concept_url')->on('th_concept');
            $table->foreign('thesaurus_root_id')->references('concept_url')->on('th_concept');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('attributes');
    }
}
